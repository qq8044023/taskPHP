<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
namespace taskphp;
/**
 * 队列接口
 * 支持多进程, 支持各种数据类型的存储
 * @author cqcqphper 小草<cqcqphper@163.com>
 */
class Queue {
    
    private static $_handler=null;
    
    /**
     * @param string $queue_name 队列名称
     * @param array  $config     配置文件
     *
     * ========== Shm ==========
     * [
     *     'drive' => 'Shm',
     *     'options'    => [

     *     ']
     * ]
     * ========== Sqlite ==========
     * [
     *     'drive' => 'Sqlite',
     *     'options'    => [
     *           'dsn'=>'queue.db',
     *     ']
     * ]
     * ========== MySQL ==========
     * [
     *     'drive' => 'Mysql',
     *     'options'    => [
     *        'name' => 'taskphp',
     *        'host'        => '127.0.0.1',//主机
     *        'port'        => '3306',//端口
     *        'username'      => 'root',//用户名
     *        'password'      => '',//密码
     *        'charset'      => 'utf8',//编码
     *        'prefix' => 'dg_', //list前缀
     *     ]
     * ]
     * ========== Redis ==========
     * [
     *     'drive' => 'Redis',
     *     'options'    => [
     *         'host'   => '127.0.0.1', //主机
     *         'port'   => '6379', //端口
     *         'prefix' => 'queue', //list前缀
     *         'password'   => '',//密码
     *     ']
     * ]
     *
     * @return drive
     */
    public static function init(){
        $config=Utils::config('queue');
        $class_name='taskphp\\queue\\drives\\'.$config['drive'];
        if(!self::$_handler){
            self::$_handler=new $class_name(isset($config['options'])?$config['options']:[]);
        }
        return self::$_handler;
    }
    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public static function get($key) {
        self::init();
        $result=self::$_handler->get($key);
        return $result;
    }
    
    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @return boolen
     */
    public static function set($key, $value) {
        self::init();
        $result=self::$_handler->set($key,$value);
        return $result;
    }
    
    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public static function rm($key) {
        self::init();
        return self::$_handler->rm($key);
    }
    
    /**
     * 删除 key 集合中的子集
     * @param unknown $key
     * @param unknown $son_key
     * @return boolean
     */
    public static function srem($key,$son_key){
        $data= (array) self::get($key);
        if(!count($data)){
            return false;
        }
        foreach ($data as $k=>$v){
            if($v==$son_key){
                unset($data[$k]);
            }
        }
        unset($data[$son_key]);
        return self::set($key, $data);
    }
    
    
    /**
     * 加入
     * @param string $key 表头
     * @param string $value 值
     */
    public static function push($key,$value){
        self::init();
        return self::$_handler->push($key,$value);
    }
    /**
     * 出列 堵塞 当没有数据的时候，会一直等待下去
     * @param string $key 表头
     * @param number $timeout 延时   0无限等待
     * @return Ambigous <NULL, mixed>
     */
    public static function pop($key,$timeout=0){
        self::init();
        $res=self::$_handler->pop($key,$timeout);
        return $res;
    }
    
    public function close(){
        self::$_handler=null;
        return true;
    }
}