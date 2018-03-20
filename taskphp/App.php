<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
namespace taskphp;
use taskphp\socket\Server as SocketServer;
/**
 * 系统主控制类
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class App{
    /**
     * socket服务对象
     * @var SocketServer
     */
    private static $_socket;
    
    /**
     * 请求方式
     * @var string
     */
    private static $_method;
    /**
     * 请求的参数
     * @var string
     */
    private static $_queryString;
    
    /* request stdin */
    private static $_queryEntity;
    
    private static $_request=array();
    
    public static $_process_list=[
        'distribute'=>[
            'file_name'=>'distribute_listen',
            'worker_count'=>1,
            'pid'=>[],
        ],
    ];
    private static $_sys_pids=[];
    
    /**
     * 运行框架
     */
    public static function run(){
        !extension_loaded('sockets') && Ui::displayUI('ERROR:Please open the sockets module');
        
        Command::analysis();
        if(!isset(Command::$_cmd_list[Command::$_cmd_key]) || !method_exists(new static,Command::$_cmd_key)){
            $text .= 'Available commands: '.PHP_EOL;
            foreach (Command::$_cmd_list as $key=>$val){
                $text.='  '.$key.' [options]'.PHP_EOL;
            }
            Ui::displayUI($text);
        }
        self::{Command::$_cmd_key}(Command::$_cmd_value);
    }
    
    /**
     * 启动任务进程
     */
    public static function start($value='all'){
        register_shutdown_function(function(){
            self::shutdown_function();
        });
        foreach (self::$_process_list as $key=>$val){
            self::$_process_list[$key]['pid'][]=popen('php '.CORE_PATH.DS.self::$_process_list[$key]['file_name'].EXT, 'r');
            Ui::showLog('distribute start success');
        }  
        if($value==='all'){
            $list=Utils::config('task_list');
        }else{
            $list=[$value=>Utils::config('task_list.'.$value)];
        }
        if(is_array($list) && count($list)){
            foreach ($list as $key=>$val){
                self::$_process_list[$key]=$list[$key];
                if(!isset(self::$_process_list[$key]['worker_count']))self::$_process_list[$key]['worker_count']=1;
                self::$_process_list[$key]['pid']=[];
                Utils::cache('listen'.$key,'true');
                Utils::cache('close_worker','false');
                if(self::$_process_list[$key]['worker_count']){
                    for($i=1;$i<=self::$_process_list[$key]['worker_count'];$i++){
                        self::$_process_list[$key]['pid'][] = popen('php '.CORE_PATH.DS.'worker_listen'.EXT.' '.$key, 'r');
                    }
                }
                Ui::showLog($key.' start success');
            }
        }
        Ui::statusUI();
        Ui::statusProcess(self::$_process_list);
        //运行web服务器
        self::init_server();
    }
    
    
    /**
     * 重启任务进程
     */
    public static function restart($value='all'){
        if($value==='all'){
            $list=Utils::config('task_list');
        }else{
            $list=[$value=>Utils::config('task_list.'.$value)];
        }
        if(is_array($list) && count($list)){
            foreach ($list as $key=>$val){
                Utils::cache('listen'.$key,'false');
                Utils::cache('close_worker','true');
                sleep(5);
                //关闭任务进程
                if(isset(self::$_process_list[$key]['pid'])){
                    foreach (self::$_process_list[$key]['pid'] as &$pid){
                        pclose($pid);
                    }
                }
                Ui::showLog($key.' close success');
                self::$_process_list[$key]=$list[$key];
                if(!isset(self::$_process_list[$key]['worker_count']))self::$_process_list[$key]['worker_count']=1;
                self::$_process_list[$key]['pid']=[];
                if(self::$_process_list[$key]['worker_count']){
                    Utils::cache('listen'.$key,'true');
                    Utils::cache('close_worker','false');
                    for($i=1;$i<=self::$_process_list[$key]['worker_count'];$i++){
                        self::$_process_list[$key]['pid'][] = popen('php '.CORE_PATH.DS.'worker_listen'.EXT.' '.$key, 'r');
                    }
                }
                Ui::showLog($key.' start success');
            }
        }
        return 'ok';
    }
    
    /**
     * 关闭任务进程
     */
    public static function close($value='all'){
        if($value==='all'){
            $list=Utils::config('task_list');
        }else{
            $list=[$value=>Utils::config('task_list.'.$value)];
        }
        if(is_array($list) && count($list)){
            foreach ($list as $key=>$val){
                Utils::cache('listen'.$key,'false');
                Utils::cache('close_worker','true');
                sleep(5);
                //关闭任务进程
                if(isset(self::$_process_list[$key]['pid'])){
                    foreach (self::$_process_list[$key]['pid'] as &$pid){
                        pclose($pid);
                    }
                }
                self::$_process_list[$key]['pid']=[];
            }
            Ui::showLog($key.' close success');
        }
        if($value==='all'){
             $is_daemon=self::is_daemon(array("main".EXT));
    	     if($is_daemon){
    	       foreach (self::$_sys_pids as &$pid){
    	           if(DS=='\\'){
    	               system('taskkill /f /t /im php.exe');
    	           }else{
    	               posix_kill($pid, SIGTERM);
    	               system('kill -9 '.$pid);
    	           }
    	           Ui::showLog($pid.' close success');
    	       }
    	     }
        }
        return 'ok';
    }
    
    private static function init_server(){
        self::$_socket = new SocketServer(Utils::config('web_manage.address'), Utils::config('web_manage.port'));
        //监听
        self::$_socket->listen();
        while(true){
            //连接
            self::$_socket->accept();
            //处理请求
            self::acceptRequest();
        }
        self::$_socket->closeListenFD();
    }
    private static function acceptRequest(){
        //根据请求状态行解析出method,query_string,filename
        $line = self::$_socket->readLine();
        $statusLineArr = explode(' ', trim($line));
        if (!is_array($statusLineArr) || count($statusLineArr) !== 3) {
            Ui::showLog('parse request status line err');
            return false;
        }
        list(self::$_method, $requestUri, $protocal) = $statusLineArr;
        if (strpos($requestUri, '?') !== false) {
            $filename    = strstr($requestUri, '?', true);
            self::$_queryString = trim(strstr($requestUri, '?'), '?');
        }else{
            $filename    = $requestUri;
            self::$_queryString = '';
        }
    
        //只支持GET和POST方法
        if (self::$_method !== 'POST' && self::$_method !== 'GET') {
            self::headers();
            self::$_socket->write('Only support GET and POST methods');
            self::$_socket->closeConnectFD();
            return ;
        }
    
        //解析缓冲区剩余数据,GET就丢弃header头,POST则解析请求体
        self::parseQueryEntity();
    
        /* 获取get和post的值  */
        //解析post 的数据
        parse_str(self::$_queryEntity,$_POST);
        //解析 get数据
        parse_str(self::$_queryString,$_GET);
    
        if(self::$_method=='POST'){
            $html=json_encode($_POST);
        }else{
            if($_GET){
                $html='taskPHP';
                if($_GET['action']=='cmd'){
                    $in_cmd=['restart','close'];
                    if(in_array($_GET['content'], $in_cmd)){
                        $argv=isset($_GET['argv'])?$_GET['argv']:'all';
                        $result=self::{$_GET['content']}($argv);
                    }
                    $html.= $_GET['action'].' '.$_GET['content'].' '.$argv.' run ok';
                    $html.=' result:'.$result;
                }elseif($_GET['action']=='task'){
                    if($_GET['content']=='select'){
                        $html.= "------------------------ taskPHP task_list ---------------------".PHP_EOL;
                        $html.= "task_name".str_pad('', 14). "run_time".str_pad('', 21)."next_time".PHP_EOL;
                        $TaskManage = new \core\lib\TaskManage();
                        foreach ($TaskManage->run_worker_list() as $item){
                            $worker=$item->get_worker();
                            $html.= str_pad($worker->get_name(), 20).\core\lib\Timer::timer_to_string($worker->get_timer()). str_pad('', 10). date("Y-m-d H:i:s",$item->get_run_time()).PHP_EOL;
                        }
                    }elseif($_GET['content']=='reload'){
                        $TaskManage = new \core\lib\TaskManage();
                        $TaskManage->load_worker();
                        $html='task reload ok'.PHP_EOL;
                        $html.= "task_name".str_pad('', 14). "run_time".str_pad('', 21)."next_time".PHP_EOL;
                        $TaskManage = new \core\lib\TaskManage();
                        foreach ($TaskManage->run_worker_list() as $item){
                            $worker=$item->get_worker();
                            $html.= str_pad($worker->get_name(), 20).\core\lib\Timer::timer_to_string($worker->get_timer()). str_pad('', 10). date("Y-m-d H:i:s",$item->get_run_time()).PHP_EOL;
                        }
                    }elseif($_GET['content']=='delete'){
                        $argv=$_GET['argv'];
                        if(!$argv){
                            $html='specify the argv of the task to delete';
                        }else{
                            $TaskManage = new \core\lib\TaskManage();
                            $TaskManage->del_worker($argv);
                            $html= $argv.' delete ok';
                        }
    
                    }else{
                        $html= "------------------------- taskPHP ------------------------------".PHP_EOL;
                        $html.= 'taskPHP version:' . TASKPHP_VERSION . "      PHP version:".PHP_VERSION.PHP_EOL;
                        $html.= 'author:码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com'.PHP_EOL;
                        $html.= 'license1:https://github.com/qq8044023/taskPHP'.PHP_EOL;
                        $html.= 'license2:https://git.oschina.net/cqcqphper/taskPHP'.PHP_EOL;
                    }
                }
    
            }else{
                
                $cmd_html='<form id="form1" name="form1" method="post" action=""><tr align="center"  bgcolor="#F2F4F6" ><td  align="left" ><select id="cmd_action"><option value="cmd">cmd</option></select><select id="cmd_content"><option value="restart">重启任务进程</option><option value="close">关闭任务进程</option></select>  参数:<input name="cmd_argv" type="text" id="cmd_argv" size="10" value="all" /><input type="button" onclick="cmd_ajax();" value="确定" /></td></tr></form>';
                $task_html='<form id="form2" name="form2" method="post" action=""><tr align="center"  bgcolor="#F2F4F6" ><td  align="left" ><select id="task_action"><option value="task">task</option></select><select id="task_content"><option value="select">查询任务</option><option value="reload">重载任务</option><option value="delete">删除任务</option></select>  参数:<input name="task_argv" type="text" id="task_argv" size="10" value="all" /><input type="button" onclick="task_ajax();" value="确定" /></td></tr></form>';
                $content='<select id="cmd_content">';
                $html='<!DOCTYPE html>
                <html>
                <meta charset="utf-8" />
                <title>hello taskPHP</title>
                <script type="text/javascript">
                    function cmd_ajax(){
                    	var xmlhttp;
                    	if (window.XMLHttpRequest){
                    		xmlhttp=new XMLHttpRequest();
                    	}else{
                    		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
                    	}
                    	xmlhttp.onreadystatechange=function(){
                    		if (xmlhttp.readyState==4 && xmlhttp.status==200){
                    			document.getElementById("result").innerHTML=xmlhttp.responseText;
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
                    function task_ajax(){
                    	var xmlhttp;
                    	if (window.XMLHttpRequest){
                    		xmlhttp=new XMLHttpRequest();
                    	}else{
                    		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
                    	}
                    	xmlhttp.onreadystatechange=function(){
                    		if (xmlhttp.readyState==4 && xmlhttp.status==200){
                    			document.getElementById("result").innerHTML=xmlhttp.responseText;
                    		}
                    	}
                    	var task_action_object=document.getElementById("task_action");
                    	var task_action_index=task_action_object.selectedIndex;
                    	var task_action_value=task_action_object.options[task_action_index].value;
           
                    	var task_content_object=document.getElementById("task_content");
                    	var task_content_index=task_content_object.selectedIndex;
                    	var task_content_value=task_content_object.options[task_content_index].value;
           
                    	var task_argv_object=document.getElementById("task_argv");
                    	var task_argv_value=task_argv_object.value;
                    	var url=document.domain+"/?action="+task_action_value+"&content="+task_content_value+"&argv="+task_argv_value;
                    	xmlhttp.open("GET",url,true);
                    	xmlhttp.send();
                    }
                    </script>
                <body>
                <table border="0" width="98%" align="center" cellpadding="1" cellspacing="1" class="tbtitle" style="margin-left:1%;"><tr><td bgcolor="#F2F4F6"><strong>taskPHP远程管理器</strong></td></tr>'.$cmd_html.$task_html.'<tr align="center" bgcolor="#FFFFFF"><td align="left"><textarea id="result"  style="width:700px; height:400px">hello taskPHP</textarea></td></tr></table>
                </body>
                </html>';
    
            }
        }
        self::headers();
        self::$_socket->write($html);
    
        self::$_socket->closeConnectFD();
    }
    
    private static function parseQueryEntity(){
        $contentLength=0;
        if (self::$_method == 'GET') {
            do {
                $line = self::$_socket->readLine();
            } while (!empty($line)); // \r\n返回空
        } else {
            do {
                $line = self::$_socket->readLine();
                if (strpos($line, 'Content-Length:') !== false) {
                    $contentLength = intval(trim(str_replace('Content-Length:', '', $line)));
                }
    
                if (strpos($line, 'Content-Type:') !== false) {
                    $contentType = trim(str_replace('Content-Type:', '', $line));
                }
            } while (!empty($line));
    
            if (empty($contentLength)) {
                Ui::showLog('POST RQUEST CONTENT-LEHGTH IS NULL');
                return false;
            }
    
            //读取消息体
            self::$_queryEntity = self::$_socket->read($contentLength);
        }
    }
    
    private static function headers(){
        $response = "HTTP/1.1 200 OK".PHP_EOL;
        $response .= 'Server: lzx-tiny-httpd/0.1.0'.PHP_EOL;
        $response .= (self::$_method == 'POST' || !empty(self::$_queryString)) ? 'Content-Type: application/json;charset=utf-8'.PHP_EOL : 'Content-Type: text/html'.PHP_EOL;
        $response .= PHP_EOL;
        self::$_socket->write($response);
    }
    /**
     * 后台进程是否在运行
     * @param array $process_name
     * @return boolean
     */
    private static function is_daemon($process_name=array()){
        if (count($process_name)==0){
            if (DS=='\\'){
                $process_name=array("main".EXT);
            } else{
                $list=[];
                $files=glob(CORE_PATH.DS.'*_listen'.EXT);
                foreach($files as $file){
                    $regex='/.*?core(.*?)_listen\.php.*?/';
                    preg_match_all($regex, $file, $matches);
                    $name=trim($matches[1][0],DS);
                    $list[$name]=$file;
                }
                foreach ($list as $key=>$val){
                    $process_name[]=$key.'_listen'.EXT;
                }
            }
        }
        ob_start();
        if (DS!=='\\'){
            system('ps aux');
        }
        else{
            system('wmic  process where caption="php.exe" get caption,commandline /value');
        }
        $ps=ob_get_contents();
        ob_end_clean();
        $ps = explode("\n", $ps);
        $list=array();
        //取出进程列表
        foreach ($ps as &$item){
            $item=trim($item);
            foreach ($process_name as &$pn){
                if(strpos($item, $pn)){
                    if (DS!='\\'){//非win
                        $item_arr=explode(' ', $item);
                        $item_arr=array_filter($item_arr);
                        $item_arr=array_merge($item_arr);
                        $list[]=$item_arr[1];
                    }else{
                        $list[]='php.exe';
                    }
    
                }
            }
        }
        self::$_sys_pids=$list;
        if(count(self::$_sys_pids)){
            return true;
        }
        return false;
    }
    public static function shutdown_function(){
        Utils::log('taskPHP daemon pid:'.getmypid().' Stop');
        foreach (self::$_process_list as $key=>$val){
            foreach (self::$_process_list[$key]['pid'] as &$pid){
                pclose($pid);
                Ui::showLog($key.' daemon Stop');
            }
        }
    }
}