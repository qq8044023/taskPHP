<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
namespace taskphp\queue\drives;
use taskphp\Utils;
/**
 * 队列驱动-Shm
 * @author cqcqphper 小草<cqcqphper@163.com>
 */
class Shm{
    /**
     * 设置属性
     * @var array
     */
    private $_options=array(
            'size'      => 512000,
            'project'   => 's',
        );
    
    private $_handler=null;
    
    public function __construct(array $options){
        if(!extension_loaded('shmop')){
            \taskphp\Console::log('ERROR:shmop module has not been opened');die;
        }
        $this->_options = array_merge($this->_options,$options);
        if(!isset($this->_options['temp'])){
            $this->_options['temp']=START_PATH.DS.'logs';
        }
        $this->_handler=$this->_ftok($this->_options['project']);
    }
    
    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name = false) {
        $shmid = @shmop_open($this->_handler, 'w', 0600, 0);
        if ($shmid !== false) {
            $size=shmop_size($shmid);
            $str=shmop_read($shmid, 0, $size);
            $str=trim($str);
            $ret = unserialize($str);
            shmop_close($shmid);
            if ($name === false) {
                return $ret;
            }
            if(isset($ret[$name])) {
                $content   =  $ret[$name];
                return $content;
            }else {
                return null;
            }
        }else {
            return false;
        }
    }
    
    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @return boolen
     */
    public function set($name, $value) {
        $lh = self::_lock();
        $val = self::get();
        if (!is_array($val)) $val = array();
        $val[$name] = $value;
        $val = serialize($val);
        if(self::_write($val, $lh)) {
            return true;
        }
        return false;
    }
    
    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function rm($name) {
        $lh = $this->_lock();
        $val = $this->get();
        if (!is_array($val)) $val = array();
        unset($val[$name]);
        $val = serialize($val);
        return $this->_write($val, $lh);
    }
    
    /**
     * 生成IPC key
     * @access private
     * @param string $project 项目标识名
     * @return integer
     */
    private static function _ftok($project) {
        if (function_exists('ftok')) {
            return ftok(__FILE__, $project);
        }
        if (DS == '\\') {
            $s = stat(__FILE__);
            return sprintf("%u", (($s['ino'] & 0xffff) | (($s['dev'] & 0xff) << 16) |
                (($project & 0xff) << 24)));
        } else {
            $filename = __FILE__ . (string) $project;
            for ($key = array(); sizeof($key) < strlen($filename); $key[] = ord(substr($filename, sizeof($key), 1)));
            return dechex(array_sum($key));
        }
    }
    
    /**
     * 写入操作
     * @access private
     * @param string $name 缓存变量名
     * @return integer|boolen
     */
    private function _write(&$val, &$lh) {
        $shmid  = shmop_open($this->_handler, 'c', 0600, $this->_options['size']);
        if ($shmid) {
            $ret = shmop_write($shmid, $val, 0) == strlen($val);
            shmop_close($shmid);
            $this->_unlock($lh);
            return $ret;
        }
        $this->_unlock($lh);
        return false;
    }
    
    /**
     * 共享锁定
     * @access private
     * @param string $name 缓存变量名
     * @return boolen
     */
    private function _lock() {
        if (function_exists('sem_get')) {
            $fp = sem_get($this->_handler, 1, 0600, 1);
            sem_acquire($fp);
        } else {
            $fp = fopen($this->_options['temp'].DS.md5($this->_handler).'.sem', 'w');
            flock($fp, LOCK_EX);
        }
        return $fp;
    }
    
    /**
     * 解除共享锁定
     * @access private
     * @param string $name 缓存变量名
     * @return boolen
     */
    private function _unlock(&$fp) {
        if (function_exists('sem_release')) {
            sem_release($fp);
        } else {
            fclose($fp);
        }
    }
    /**
     * 加入
     * @param string $key 表头
     * @param string $value 值
     */
    public function push($key,$value){
        $data= (array) $this->get($key);
        array_push($data,$value);
        return $this->set($key, $data);
    }
    /**
     * 出列 堵塞 当没有数据的时候，会一直等待下去
     * @param string $key 表头
     * @param number $timeout 延时   0无限等待
     * @return Ambigous <NULL, mixed>
     */
    public function pop($key,$timeout=0){
        $res=null;
        $wh=true;$second=0;
        while ($wh && Utils::cache('close_worker')=='false'){
            $data= (array) $this->get($key);
            if(count($data)!=0){
                $res=array_shift($data);
                self::set($key, $data);
                $wh=false;
                break;
            }
            
            if($timeout==0){
                sleep(1);
            }elseif($timeout>0 && $second<$timeout){
                sleep(1);
                $second++;
            }else{
                break;
            }
        }
        return $res;
    }
    /**
     * 删除 key 集合中的子集
     * @param unknown $key
     * @param unknown $son_key
     * @return boolean
     */
    public function srem($key,$son_key){
        $data= (array) $this->get($key);
        if(!count($data)){
            return false;
        }
        foreach ($data as $k=>$v){
            if($v==$son_key){
                unset($data[$k]);
            }
        }
        unset($data[$son_key]);
        return $this->set($key, $data);
    }
    
    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear(){
        $shmid = @shmop_open($this->_handler, 'w', 0600, 0);
        if($shmid){
            shmop_delete($shmid);
        }
        return true;
    }
}