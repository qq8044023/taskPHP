<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
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
            'process_name'=>'distribute',
            'worker_count'=>1,
            'pid'=>[],
        ],
    ];
    private static $_sys_pids=[];
    /**
     * 运行框架
     */
    public static function run(){
        $argv=$_SERVER['argv'];
        if(strpos($_SERVER['PHP_SELF'], DS)!==false){
            $argv[0]=$_SERVER['PHP_SELF'];
        }else{
            $argv[0]=START_PATH.DS.$_SERVER['PHP_SELF'];
        }
        Command::setArgv($argv);
        Command::analysis();
        if(!isset(Command::$_cmd_list[Command::$_cmd_key]) || !method_exists(new static,Command::$_cmd_key)){
            $text = 'Available commands: '.PHP_EOL;
            foreach (Command::$_cmd_list as $key=>$val){
                $text.='  '.$key.' [options]'.PHP_EOL;
            }
            Console::display($text);
        }
        if(PHP_SAPI!=='cli')Console::display('Can only run in the PHP cli mode');
        self::{Command::$_cmd_key}(Command::$_cmd_value);
    }
    
    /**
     * 启动任务进程
     */
    public static function start($value='all'){
        register_shutdown_function(function(){
            self::shutdown_function();
        });
        if(!function_exists('popen')){
            Console::log('ERROR: function popen is disabled');die;
        }
        Console::hreader();
        
        if($value==='all'){
            $list=Utils::config('task_list');
        }else{
            $list=[$value=>Utils::config('task_list.'.$value)];
        } 
        $progress=0;
        $progress_count=self::compute_process_count($list);
        printf("progress: [%-50s] %d%%\r", str_repeat('#',$progress/$progress_count*50), $progress/$progress_count*100);
        sleep(1);
        foreach (self::$_process_list as $key=>$val){
            self::$_process_list[$key]['pid'][]=popen(self::get_path().' '.Command::$_cmd.' '.self::$_process_list[$key]['process_name'], 'r');
            $progress++;
            printf("progress: [%-50s] %d%%\r", str_repeat('#',$progress/$progress_count*50), $progress/$progress_count*100);
            sleep(1);
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
                        self::$_process_list[$key]['pid'][] = popen(self::get_path().' '.Command::$_cmd.' worker '.$key, 'r');
                        $progress++;
                        printf("progress: [%-50s] %d%%\r", str_repeat('#',$progress/$progress_count*50), $progress/$progress_count*100);
                        sleep(1);
                    }
                }
            }
        }
        Console::process_list(self::$_process_list);
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
                Console::log($key.' close success');
                self::$_process_list[$key]=$list[$key];
                if(!isset(self::$_process_list[$key]['worker_count']))self::$_process_list[$key]['worker_count']=1;
                self::$_process_list[$key]['pid']=[];
                if(self::$_process_list[$key]['worker_count']){
                    Utils::cache('listen'.$key,'true');
                    Utils::cache('close_worker','false');
                    for($i=1;$i<=self::$_process_list[$key]['worker_count'];$i++){
                        self::$_process_list[$key]['pid'][] = popen(self::get_path().' '.Command::$_cmd.' worker '.$key, 'r');
                    }
                }
                Console::log($key.' start success');
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
            Console::log($key.' close success');
        }
        if($value==='all'){
             $is_daemon=self::is_daemon(array($_SERVER['PHP_SELF']));
    	     if($is_daemon){
    	       foreach (self::$_sys_pids as &$pid){
    	           if(DS=='\\'){
    	               system('taskkill /f /t /im php.exe');
    	           }else{
    	               if(extension_loaded('posix'))posix_kill($pid, SIGTERM);
    	               system('kill -9 '.$pid);
    	           }
    	           Console::log($pid.' close success');
    	       }
    	     }
        }
        return 'ok';
    }
    
    private static function init_server(){
        self::$_socket = new SocketServer(Utils::config('web_manage.address'), Utils::config('web_manage.port'));
        //监听
        self::$_socket->listen();
		/* 要监听的三个sockets数组 */
		$read_socks = [];
		$write_socks = [];
		$except_socks = [];  // 注意 php 不支持直接将NULL作为引用传参，所以这里定义一个变量
		$read_socks[] = self::$_socket->_listenFD;

        while(true){
			/* 这两个数组会被改变，所以用两个临时变量 */
			$tmp_reads = $read_socks;
			$tmp_writes = $write_socks;
			$count = socket_select($tmp_reads, $tmp_writes, $except_socks, NULL);  // timeout 传 NULL 会一直阻塞直到有结果返回
			foreach ($tmp_reads as $read){
				if ($read == self::$_socket->_listenFD){
					//连接
					$connsock=self::$_socket->accept();
					if ($connsock){
						// 把新的连接sokcet加入监听
						$read_socks[] = $connsock;
						$write_socks[] = $connsock;
						\taskphp\Console::log('new client connect server');
					}
				}else{
					$line = self::$_socket->readLine($read);
					if ($line){
						if (in_array($read, $tmp_writes)){
							//处理请求
							self::acceptRequest($read,$line);
						}
					}else{
						self::$_socket->closeConnectFD($read);
					}
					//移除对该 socket 监听
					foreach ($read_socks as $key => $val){
						if ($val == $read) unset($read_socks[$key]);
					}
					foreach ($write_socks as $key => $val){
						if ($val == $read) unset($write_socks[$key]);
					}
					\taskphp\Console::log('client close');
				}
			}
        }
        self::$_socket->closeListenFD();
    }
    private static function acceptRequest($connect,$line){
        //根据请求状态行解析出method,query_string,filename
        $statusLineArr = explode(' ', trim($line));
        if (!is_array($statusLineArr) || count($statusLineArr) !== 3) {
            Console::log('parse request status line err');
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
            self::headers($connect);
            self::$_socket->write($connect,'Only support GET and POST methods');
            self::$_socket->closeConnectFD($connect);
            return ;
        }
    
        //解析缓冲区剩余数据,GET就丢弃header头,POST则解析请求体
        self::parseQueryEntity($connect);
    
        /* 获取get和post的值  */
        //解析post 的数据
        parse_str(self::$_queryEntity,$_POST);
        //解析 get数据
        parse_str(self::$_queryString,$_GET);
    
        if(self::$_method=='POST'){
            $html=json_encode($_POST);
        }else{
            if($_GET){
                $html='taskPHP'.PHP_EOL;
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
                        $html.= "task_name".str_pad('', 14). "run_time".str_pad('', 21)."next_time".PHP_EOL;
                        $TaskManage = new TaskManage();
                        foreach ($TaskManage->run_worker_list() as $item){
                            $worker=$item->get_worker();
                            $html.= str_pad($worker->get_name(), 20).Crontab::crontab_to_string($worker->get_crontab()). str_pad('', 10). date("Y-m-d H:i:s",$item->get_run_time()).PHP_EOL;
                        }
                    }elseif($_GET['content']=='reload'){
                        $TaskManage = new TaskManage();
                        $TaskManage->load_worker();
                        $html='task reload ok'.PHP_EOL;
                        $html.= "task_name".str_pad('', 14). "run_time".str_pad('', 21)."next_time".PHP_EOL;
                        $TaskManage = new TaskManage();
                        foreach ($TaskManage->run_worker_list() as $item){
                            $worker=$item->get_worker();
                            $html.= str_pad($worker->get_name(), 20).Crontab::crontab_to_string($worker->get_crontab()). str_pad('', 10). date("Y-m-d H:i:s",$item->get_run_time()).PHP_EOL;
                        }
                    }elseif($_GET['content']=='delete'){
                        $argv=$_GET['argv'];
                        if(!$argv){
                            $html='specify the argv of the task to delete';
                        }else{
                            $TaskManage = new TaskManage();
                            $TaskManage->del_worker($argv);
                            $html= $argv.' delete ok';
                        }
    
                    }else{
                        $html= 'taskPHP version:' . TASKPHP_VERSION . "      PHP version:".PHP_VERSION.PHP_EOL;
                    }
                }
    
            }else{
                $html=file_get_contents(TASKPHP_PATH.DS.'tpl'.DS.'web_manage_html.tpl');
            }
        }
        self::headers($connect);
        self::$_socket->write($connect,$html);
    
        self::$_socket->closeConnectFD($connect);
    }
    
    private static function parseQueryEntity($connect){
        $contentLength=0;
        if (self::$_method == 'GET') {
            do {
                $line = self::$_socket->readLine($connect);
            } while (!empty($line)); // \r\n返回空
        } else {
            do {
                $line = self::$_socket->readLine($connect);
                if (strpos($line, 'Content-Length:') !== false) {
                    $contentLength = intval(trim(str_replace('Content-Length:', '', $line)));
                }
    
                if (strpos($line, 'Content-Type:') !== false) {
                    $contentType = trim(str_replace('Content-Type:', '', $line));
                }
            } while (!empty($line));
    
            if (empty($contentLength)) {
                Console::log('POST RQUEST CONTENT-LEHGTH IS NULL');
                return false;
            }
    
            //读取消息体
            self::$_queryEntity = self::$_socket->read($connect,$contentLength);
        }
    }
    
    private static function headers($connect){
        $response = "HTTP/1.1 200 OK".PHP_EOL;
        $response .= 'Server: lzx-tiny-httpd/0.1.0'.PHP_EOL;
        $response .= (self::$_method == 'POST' || !empty(self::$_queryString)) ? 'Content-Type: application/json;charset=utf-8'.PHP_EOL : 'Content-Type: text/html'.PHP_EOL;
        $response .= PHP_EOL;
        self::$_socket->write($connect,$response);
    }
    /**
     * 后台进程是否在运行
     * @param array $process_name
     * @return boolean
     */
    private static function is_daemon($process_name=array()){
        if (count($process_name)==0){
            $process_name=array($_SERVER['PHP_SELF']);
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
    
    private static function distribute(){
        $taskManage=new TaskManage();
        $taskManage->load_worker();
        $distribute=new Distribute();
        $distribute->set_task_manage($taskManage)->listen();
    }
    
    private static function worker(){
        WorkerExe::instance()->listen(Command::$_cmd_value);
    }
    
    private static function get_path(){
        $php_path=Utils::config('php_path');
        if(!$php_path){
            $php_path='php';
            if(isset($_SERVER['Path'])){
                $path_arr=explode(';', $_SERVER['Path']);
                foreach ($path_arr as $item){
                    if(strpos($item, 'php')!==false){
                        if(DS=='\\'){//win
                            $filename=$item.DS.'php.exe';
                        }else{
                            $filename=$item.DS.'php';
                        }
                        if(!is_file($filename)){
                            continue;
                        }
                        if(strpos($filename, ' ')!==false){
                            $text='php path cannot have space';
                            Console::display($text);
                        }
                        $php_path=$item.DS.'php';
                        continue;
                    }
                }
            }elseif(isset($_SERVER['_'])){
                $php_path=$_SERVER['_'];
            }
        }
        return $php_path;
    }
    
    public static function get_phpini_path(){
        ob_start();
        system(self::get_path().' --ini');
        $ps=ob_get_contents();
        ob_end_clean();
        $ps = explode("\n", $ps);
        $phpini_path='';
        foreach ($ps as $line){
            if(strpos($line, 'Loaded Configuration File:')!==false){
                $phpini_path=str_replace('Loaded Configuration File:', '', $line);
                continue;
            }
        }
        return trim($phpini_path);
    }
    public static function compute_process_count($list){
        $process_count=1;
        foreach ($list as $task){
            $process_count+=$task['worker_count'];
        }
        return $process_count;
    }
    public static function shutdown_function(){
        Utils::log('taskPHP daemon pid:'.getmypid().' Stop');
        foreach (self::$_process_list as $key=>$val){
            foreach (self::$_process_list[$key]['pid'] as &$pid){
                pclose($pid);
                Console::log($key.' daemon Stop');
            }
        }
    }
}