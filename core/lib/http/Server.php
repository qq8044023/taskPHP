<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
namespace core\lib\http;
use core\lib\socket\Server as SocketServer;
use core\lib\Exception;
use core\lib\Utils;
/**
 * http服务端
 * @author cqcqphper 小草<cqcqphper@163.com>
 */
class Server{
    /**
     * 服务配置
     * @var array
     */
    private $_config;
    /**
     * socket服务对象
     * @var SocketServer
     */
    private $_socket;

    /**
     * 请求方式
     * @var string
     */
    private $_method;
    /**
     * 请求的路径
     * @var string
     */
    private $_requestUri;
    /**
     * 请求的参数
     * @var string
     */
    private $_queryString;
    /**
     * 请求的文件
     * @var unknown
     */
    private $_filename;
    
    private $_contentType;
    private $_contentLength;

    /* request stdin */
    private $_queryEntity;

    private $_request=array();
    /* resp Server */
    const RESP_SERVER = "Server: lzx-tiny-httpd/0.1.0";
    const RESP_CONTENT_TYPE = "Content-Type: text/html";

    /* cgi请求返回json类型postman才能友好显示 */
    const RESP_CGI_CONTENT_TYPE = "Content-Type: application/json;charset=utf-8";
    
    const METHOD_POST = 'POST';
    const METHOD_GET = 'GET';

    public function __construct($config){
        //配置
        $this->_config = $config;
        //检查端口
        if(Utils::checkPortBindable('127.0.0.1', $this->_config['port'])){
            //socket
            $this->_socket = new SocketServer($this->_config['address'], $this->_config['port']);
        }
    }

    public function listen(){
        if($this->_socket===null){
            Utils::log($this->_config['port'].'端口已被占用,打开httpserver_listen.php文件重新定义端口号!',3);
            return false;
        }
        //监听
        $this->_socket->listen();
        while(true){
            //连接
            $this->_socket->accept();
            //处理请求
            $this->acceptRequest();
        }
        $this->_socket->closeListenFD();
    }

    public function acceptRequest(){
        //根据请求状态行解析出method,query_string,filename
        $this->parseQueryStatusLine();
        
        //只支持GET和POST方法
        if ($this->_method !== self::METHOD_POST && $this->_method !== self::METHOD_GET) {
            return $this->error('Only support GET and POST methods');
        }

        //解析缓冲区剩余数据,GET就丢弃header头,POST则解析请求体
        $this->parseQueryEntity();
        
        /* 获取get和post的值  */
        $this->resolveRequest();
        
        if($this->_method=='POST'){
            $html=json_encode($_POST);
        }else{
            if($_GET){
                $html='taskPHP';
                if($_GET['action']=='cmd'){
                    if($_GET['content']=='select'){
                        $html.= "------------------------ taskPHP task_list ---------------------".PHP_EOL;
                        $html.= "task_name".str_pad('', 14). "run_time".str_pad('', 21)."next_time".PHP_EOL;
                        foreach (\core\lib\TaskManage::run_worker_list() as $item){
                            $worker=$item->get_worker();
                            $html.= str_pad($worker->get_name(), 20).\core\lib\Timer::timer_to_string($worker->get_timer()). str_pad('', 10). date("Y-m-d H:i:s",$item->get_run_time()).PHP_EOL;
                        }
                    }elseif($_GET['content']=='reload'){
                        $TaskManage = new \core\lib\TaskManage();
                        $TaskManage->load_worker();
                        $html='task reload ok'.PHP_EOL;
                        $html.= "task_name".str_pad('', 14). "run_time".str_pad('', 21)."next_time".PHP_EOL;
                        foreach (\core\lib\TaskManage::run_worker_list() as $item){
                            $worker=$item->get_worker();
                            $html.= str_pad($worker->get_name(), 20).\core\lib\Timer::timer_to_string($worker->get_timer()). str_pad('', 10). date("Y-m-d H:i:s",$item->get_run_time()).PHP_EOL;
                        }
                    }elseif($_GET['content']=='loglist'){
                        $html='';
                        $worker_result = \core\lib\TaskManage::worker_result();
                        if(is_array($worker_result) && count($worker_result)){
                            foreach ($worker_result as $item){
                                list($time,$task_class,$result)=$item;
                                $html.= str_pad($task_class, 20).$time. str_pad('', 10). $result.PHP_EOL;
                            }
                        }else{
                            $html.'not task';
                        }
                        
                    }elseif($_GET['content']=='delete'){
                        $argv=$_GET['argv'];
                        if(!$argv){
                            $html='specify the argv of the task to delete';
                        }else{
                            \core\lib\TaskManage::del_worker($argv);
                            $html= $argv.' delete ok';
                        }
                        
                    }else{
                        $html= "------------------------- taskPHP ------------------------------".PHP_EOL;
                        $html.= 'taskPHP version:' . ML_VERSION . "      PHP version:".PHP_VERSION.PHP_EOL;
                        $html.= 'author:码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com'.PHP_EOL;
                        $html.= 'license1:https://github.com/qq8044023/taskPHP'.PHP_EOL;
                        $html.= 'license2:https://git.oschina.net/cqcqphper/taskPHP'.PHP_EOL;
                    }
                }
                
            }else{
                $action='<select id="cmd_action"><option value="cmd">cmd</option></select>';
                
                $content='<select id="cmd_content">';
                $cmd_list=array(
                    'select','reload','loglist','delete'
                );
                foreach ($cmd_list as $cmd){
                    $content.='<option value="'.$cmd.'">'.$cmd.'</option>';
                }
                $content.='</select>';
                
                $html='<!DOCTYPE html>
                <html>
                <meta charset="utf-8" />
                <title>hello taskPHP</title>
                <script type="text/javascript">
                    function loadXMLDoc(){
                    	var xmlhttp;
                    	if (window.XMLHttpRequest){
                    		xmlhttp=new XMLHttpRequest();
                    	}else{
                    		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
                    	}
                    	xmlhttp.onreadystatechange=function(){
                    		if (xmlhttp.readyState==4 && xmlhttp.status==200){
                    			document.getElementById("cmd_result").innerHTML=xmlhttp.responseText;
                    		}
                    	}
                    	var cmd_action_object=document.getElementById("cmd_action");
                    	var cmd_action_index=cmd_action_object.selectedIndex;
                    	var cmd_action_value=cmd_action_object.options[cmd_action_index].value;
                    	
                    	var cmd_content_object=document.getElementById("cmd_content");
                    	var cmd_content_index=cmd_content_object.selectedIndex;
                    	var cmd_content_value=cmd_content_object.options[cmd_content_index].value;
                    	
                    	var cmd_argv_object=document.getElementById("cmd_argv");
                    	var cmd_argv_value=cmd_argv_object.value;
                    	var url=document.domain+"/?action="+cmd_action_value+"&content="+cmd_content_value+"&argv="+cmd_argv_value;
                    	xmlhttp.open("GET",url,true);
                    	xmlhttp.send();
                    }
                    </script>
                <body>
                <table border="0" width="98%" align="center" cellpadding="1" cellspacing="1" class="tbtitle" style="margin-left:1%;"><tr><td bgcolor="#F2F4F6"><strong>taskPHP远程管理器</strong></td></tr><form id="form1" name="form1" method="post" action=""><tr align="center"  bgcolor="#F2F4F6" ><td  align="left" >'.$action.$content.'  参数:<input name="cmd_argv" type="text" id="cmd_argv" size="10" /><input type="button" onclick="loadXMLDoc();" value="确定" /></td></tr></form><tr align="center" bgcolor="#FFFFFF"><td align="left"><textarea id="cmd_result"  style="width:700px; height:400px"id="display">hello taskPHP</textarea></td></tr></table>
                </body>
                </html>';
                
            }
        } 
        $this->respData($html);
        
        $this->_socket->closeConnectFD();
    }

    /**
     * 解析 get 和 post的
     *   */
    protected function resolveRequest(){
        //解析post 的数据
        parse_str($this->_queryEntity,$_POST);
        //解析 get数据
        parse_str($this->_queryString,$_GET);
    }
    
    /**
     * 是否是动态请求
     *
     * @param $method
     * @param $queryString
     * @return bool
     */
    public function isCgi(){
        return ($this->_method == self::METHOD_POST || !empty($this->_queryString));
    }

    /**
     * 解析请求状态行
     *
     * @param $connfd
     */
    public function parseQueryStatusLine(){
        $line = $this->_socket->readLine();

        $statusLineArr = explode(' ', trim($line));
        if (!is_array($statusLineArr) || count($statusLineArr) !== 3) {
            \core\lib\Ui::displayUI('parse request status line err.',false);
            return false;
        }

        list($this->_method, $this->_requestUri, $protocal) = $statusLineArr;

        if (strpos($this->_requestUri, '?') !== false) {
            $this->_filename    = strstr($this->_requestUri, '?', true);
            $this->_queryString = trim(strstr($this->_requestUri, '?'), '?');
        }else{
            $this->_filename    = $this->_requestUri;
            $this->_queryString = '';
        }
    }

    public function parseQueryEntity(){
        if ($this->_method == self::METHOD_GET) {
            do {
                $line = $this->_socket->readLine();
            } while (!empty($line)); // \r\n返回空
        } else {
            do {
                $line = $this->_socket->readLine();
                if (strpos($line, 'Content-Length:') !== false) {
                    $this->_contentLength = intval(trim(str_replace('Content-Length:', '', $line)));
                }

                if (strpos($line, 'Content-Type:') !== false) {
                    $this->_contentType = trim(str_replace('Content-Type:', '', $line));
                }
            } while (!empty($line));

            if (empty($this->_contentLength)) {
                \core\lib\Ui::displayUI('POST RQUEST CONTENT-LEHGTH IS NULL.',false);
                return false;
            }

            //读取消息体
            $this->_queryEntity = $this->_socket->read($this->_contentLength);
        }
    }

    public function respData($resp){
        $this->headers();
        $this->_socket->write($resp);
    }

    public function cat($file){
        $this->headers();

        $fileObj = new \SplFileObject($file, "r");
        while (!$fileObj->eof()) {
            $line = $fileObj->fgets();
            $this->_socket->write($line);
        }
    }

    public function headers(){
        $response = "HTTP/1.1 200 OK".PHP_EOL;
        $response .= self::RESP_SERVER.PHP_EOL;
        $response .= $this->isCgi() ? self::RESP_CGI_CONTENT_TYPE.PHP_EOL : self::RESP_CONTENT_TYPE.PHP_EOL;
        $response .= PHP_EOL;
        $this->_socket->write($response);
    }

    public function error($response){
        $this->headers();
        $this->_socket->write($response);
        $this->_socket->closeConnectFD();
    }
}