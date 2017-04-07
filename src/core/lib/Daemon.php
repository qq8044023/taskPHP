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
        $os='unix';
        if(DS=='\\')$os='win';
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
        $memory_limit=Config::get('memory_limit');
        $memory_limit=$memory_limit?$memory_limit:128;
        ini_set('memory_limit',$memory_limit.'M');
        $runer_limit=Config::get('runer_limit');
        define('RUNER_LIMIT',$runer_limit);
        define("RUNER_FORK", DS != '\\' && function_exists('pcntl_fork'));
        if(RUNER_FORK){
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
}