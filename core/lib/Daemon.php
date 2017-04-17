<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
namespace core\lib;
/**
 * 守护进程类
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class Daemon{
    
    public $_listen_list=array();
    
    public $_process_list=array();
    
    public function init(){
        $this->get_listen_list();
        $this->exec();
    }
    
    /**
     * 设置进程用户
     */
    public function set_user(){
        $user=Config::get('core_user');
        if($user){
            $userinfo = posix_getpwnam($user);
        }
        foreach($argv as $value){
            if(isset($is_u)){
                $user=trim($value);
                $userinfo = posix_getpwnam($user);
                break;
            }
            if($value=='-u')$is_u=true;
        }
        if(!$userinfo['uid']){
            die("can't find user:".$user);
        }
        @posix_setuid($userinfo['uid']);
    }
    /**
     * 获取进程监控入口列表
     */
    public function get_listen_list(){
        $files=glob(CORE_PATH.DS.'*_listen'.EXT);
        foreach($files as $file){
            $regex='/.*?core(.*?)_listen\.php.*?/';
            preg_match_all($regex, $file, $matches);
            $name=trim($matches[1][0],DS);
            $this->_listen_list[$name]=$file;
        }
        return $this->_listen_list;
    }
    
    public function exec(){
        $os=Utils::get_os();
        $this->$os();
    }
    /**
     * win进程
     */
    public function win(){
        foreach ($this->_listen_list as $key=>$val){
            $this->_process[$key] = popen('php '.$val, 'r');
        }        
        Ui::statusUI();
        Ui::statusProcess($this->_listen_list);
        while (1){
            foreach ($this->_process as $key=>$val){
                $read = fread($this->_process[$key], 1048);
                echo $read;
            }
        }
    }
    
    public function worker_son(){
        ini_set('memory_limit',Config::get('memory_limit'));
        define('WORKER_LIMIT',Config::get('worker_limit'));
        define("WORKER_FORK",Utils::is_worker_fork());
        if(WORKER_FORK){
            runer_frok:
            $pid = pcntl_fork();
            if ($pid == -1) die(pcntl_get_last_error());
            elseif($pid) {
                Log::inputJson($pid.",");
                unset($pid);
                pcntl_wait($status,WUNTRACED);
                goto runer_frok;
            }
        }
    }
    
    public function unix(){
        foreach ($this->_listen_list as $key=>$val){
            $this->_process[$key]=0;
        }
        Ui::statusUI();
        Ui::statusProcess($this->_listen_list);
        Log::inputJson(getmypid().",");
        foreach ($this->_process as $key=>$value){
            if($this->_process[$key]==0){
                $this->start_creating($key);
            }
        }
        //等待子进程结束..
        $pid=pcntl_wait($status,WUNTRACED);
        foreach ($this->_process as $key=>$value){
            if($this->_process[$key]==$pid){
                $this->start_creating($key);
            }
        }
        exit('over');
    }
    
    private function start_creating($title){
        $pid = pcntl_fork();
        if ($pid == -1){
            die(pcntl_get_last_error());
        }elseif ($pid){
            Log::inputJson($pid.",");
            $this->_process[$title]=$pid;
        }else{
            $this->start_exec($title);
        }
    }
    
    private function start_exec($title){
       include $this->_listen_list[$title];
       exit;
    }
    
    /**
     * 后台进程是否在运行
     * @param array $process_name
     * @return boolean
     */
    public static function is_daemon($process_name=array()){
        $is_windows=DS=='\\';
        if (count($process_name)==0){
            if (!$is_windows){
                $process_name=array("main.php");
            } else{
                $process_name=array('distribute_listen.php','worker_listen.php');
            }
        }
        ob_start();
        if (!$is_windows){
            system('ps aux');
        }
        else{
            system('wmic  process where caption="php.exe" get caption,commandline /value');
        }
        $ps=ob_get_contents();
        ob_end_clean();
        $ps = explode("\n", $ps);
        $out=[];
        foreach ($ps as $v){
            $v=trim($v);
            if (empty($v)){
                continue;
            }
            $p=strrpos($v," ");
            if ($p===false){
                continue;
            }
            $out[]=trim(substr($v,$p));
        }
        foreach ($out as &$item){
            if(strpos($item, DS)){
                $item_arr=explode(DS, $item);
                $item=end($item_arr);
            }
            $process_name=array_merge(array_diff($process_name, array($item)));
        }
        if(count($process_name)){
            return false;
        }
    
        return true;
    }
}