<?php
namespace core\lib;
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */

/**
 * 使用数据库来实现进程通信
 * 支持多进程, 支持各种数据类型的存储
 * @author cqcqphper 小草<cqcqphper@163.com>
 */
class Queue{
    /**
     * 设置属性
     * @var array
     */
    private static $_options=array();
    
    private static $_handler=null;
    
    public static function get_connect(){
        $options=array(
            'DB_TYPE'   =>'sqlite',
            'DB_NAME'  =>LOGS_PATH.DS.'core_queue.db',
            'table'      => 'core_queue',
            'prefix'     =>'',
        );
        if (!file_exists($options['DB_NAME'])) {
            if (!($fp = fopen($options['DB_NAME'], "w+"))) Utils::log('create '.$options['DB_NAME'].' error');
            fclose($fp);
            self::$_handler=Utils::model($options['table'],$options['prefix'],$options);
            self::$_handler->query('CREATE TABLE core_queue (name varchar(200) UNIQUE,content TEXT)');
        }
        if(self::$_handler==null){
            self::$_handler=Utils::model($options['table'],$options['prefix'],$options);
        }
        self::$_options=$options;
        return self::$_handler;
    }
    
    public static function all(){
        self::get_connect();
        $sql    = 'SELECT content FROM ' . self::$_options['table'] . ' WHERE 1';
        $res=self::$_handler->query($sql);
        return null;
    }
    
    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public static function get($name) {
        self::get_connect();
        $sql    = 'SELECT content FROM ' . self::$_options['table'] . ' WHERE name=\'' . $name . '\' LIMIT 1';
        $res=self::$_handler->query($sql);
        if(is_array($res) && count($res)){
            $content=$res[0]['content'];
            return unserialize($content);
        }
        return null;
    }
    
    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @return boolen
     */
    public static function set($name, $value) {
        self::get_connect();
        $value = serialize($value);
        $sql    = 'SELECT content FROM ' . self::$_options['table'] . ' WHERE name=\'' . $name . '\' LIMIT 1';
        $res=self::$_handler->query($sql);
        if(is_array($res) && count($res)){
            $sql='UPDATE '.self::$_options['table'].'
              SET content = \''.$value.'\'
              WHERE name=\''.$name.'\'';
        }else{
            $sql='INSERT INTO '.self::$_options['table'].'
                (name, content) 
                  VALUES 
                (\''.$name.'\', \''.$value.'\')';
        }
        $res=self::$_handler->execute($sql);
        return $res;
    }
    
    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public static function rm($name) {
        self::get_connect();
        $sql='DELETE FROM '.self::$_options['table'].'
              WHERE name=\''.$name.'\'';
        $res=self::$_handler->execute($sql);
        return $res;
    }

    public function close(){
        self::$_handler=null;
        return true;
    }
    /**
     * 加入
     * @param string $key 表头
     * @param string $value 值
     */
    public static function lPush($key,$value){
        $data= (array) self::get($key);
        array_push($data,$value);
        return self::set($key, $data);
    }
    /**
     * 出列 堵塞 当没有数据的时候，会一直等待下去
     * @param string $key 表头
     * @param number $timeout 延时   0无限等待
     * @return Ambigous <NULL, mixed>
     */
    public static function brPop($key,$timeout=0){
        $res=null;
        $wh=true;$second=0;
        while ($wh){
            $data= (array) self::get($key);
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
     * @return boolean|\core\lib\boolen
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
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear(){
        self::get_connect();
        $sql = 'DELETE FROM ' . self::$_options['table'];
        $res=self::$_handler->execute($sql);
        return $res;
    }
}