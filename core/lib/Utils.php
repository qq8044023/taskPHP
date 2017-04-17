<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
namespace core\lib;
/**
 * 工具类，主要放一些常用方法
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class Utils{
    
    public static function check_worker_fork(){
        return self::get_os() != 'win' && self::check_pcntl();
    }
    
    public static function is_worker_fork(){
        $worker_mode=Config::get('worker_mode');
        if($worker_mode===0){
            if(self::check_worker_fork())return true;
        }elseif($worker_mode===1){
            if(self::check_worker_fork())return true;
        }
        return false;
    }
    
    
    public static function get_os(){
        $os='unix';
        if(DS=='\\')$os='win';
        return $os;
    }
    
    public static function check_pthreads(){
        return extension_loaded('pthreads');
    }
    public static function is_pthreads(){
        $worker_mode=Config::get('worker_mode');
        if($worker_mode===0){
            if(self::check_pthreads())return true;
        }elseif($worker_mode===3){
            if(self::check_pthreads())return true;
        }
        return false;
    }
    
    public static function is_popen(){
        $worker_mode=Config::get('worker_mode');
        if($worker_mode===0){
            if(!self::check_pthreads())return true;
        }elseif($worker_mode===2){
            return true;
        }
        return false;
    }
    
    public static function check_pcntl(){
        return extension_loaded('pcntl');
    }
}