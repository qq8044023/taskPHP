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
    
    public static $_sys_pids=array();
    
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
        if (count($process_name)==0){
            if (Utils::get_os()!=='win'){
                $process_name=array("main".EXT);
            } else{
                $list=(new static)->get_listen_list();
                foreach ($list as $key=>$val){
                    $process_name[]=$key.'_listen'.EXT;
                }
            }
        }
        ob_start();
        if (Utils::get_os()!=='win'){
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
                    if (Utils::get_os()!=='win'){//非win
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
}