<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
namespace taskphp\queue\drives;
use taskphp\Utils;
use PDO;

/**
 * 队列驱动-MySQL
 * 说明 :
 * 1 如果消息预估大于这个mysql驱动不能够使用
 */
class Mysql{
    /**
     * 设置属性
     * @var array
     */
    private  $_options=[
        'charset'       => 'utf8',
        'port'          => 3306,
        'table'=>'taskphp_queue',
    ];
    /**
     * 数据库实例
     * @var null
     */
    private $_db = null;

    /**
     * MysqlQueue constructor.
     * @param string $queue_name
     * @param array $config
     */
    public function __construct(array $options){
        if(!extension_loaded('pdo')){
            \taskphp\Console::log('ERROR:pdo module has not been opened');die;
        }
        $this->_options = array_merge($this->_options,$options);
        try {
            $dsn=sprintf(
                "mysql:host=%s;dbname=%s;port=%s;charset=%s;",
                $this->_options['host'],
                $this->_options['name'],
                $this->_options['port'],
                $this->_options['charset']
            );
            $this->_db = new PDO($dsn,$this->_options['username'],$this->_options['password']);
        }catch (\PDOException $e){
            Utils::log($e->getMessage());
        }
        
        if(!$this->tableExist()){
            $sql = "CREATE TABLE IF NOT EXISTS `{$this->_options['table']}` (
            `name` varchar(200) CHARACTER SET utf8 NOT NULL,
            `content` TEXT NOT NULL,
            PRIMARY KEY (`name`)
            ) ENGINE = InnoDB  DEFAULT CHARACTER SET = utf8";
            $this->_db->exec($sql);
        }
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        $sql    = 'SELECT content FROM ' . $this->_options['table'] . ' WHERE name=\'' . $name . '\' LIMIT 1';
        $res=$this->_db->query($sql)->fetch(PDO::FETCH_ASSOC);
        if(is_array($res) && count($res)){
            $content=$res['content'];
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
    public function set($name, $value) {
        $value = serialize($value);
        $value=addslashes($value);
        $sql    = 'SELECT content FROM ' . $this->_options['table'] . ' WHERE name=\'' . $name . '\' LIMIT 1';
        $res=$this->_db->query($sql)->fetch(PDO::FETCH_ASSOC);
        if(is_array($res) && count($res)){
            $sql='UPDATE '.$this->_options['table'].'
              SET content = \''.$value.'\'
              WHERE name=\''.$name.'\'';
        }else{
            $sql='INSERT INTO '.$this->_options['table'].'
                (name, content)
                  VALUES
                (\''.$name.'\', \''.$value.'\')';
        }
        
        $res=$res=$this->_db->exec($sql);
        return $res;
    }
    
    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function rm($name) {
        $sql='DELETE FROM '.$this->_options['table'].'
              WHERE name=\''.$name.'\'';
        $res=$this->_db->exec($sql);
        return $res;
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
        while ($wh){
            $data= (array) $this->get($key);
            if(count($data)!=0){
                $res=array_shift($data);
                $this->set($key, $data);
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
        $sql = 'DELETE FROM ' . $this->_options['table'];
        $res=$this->_db->exec($sql);
        return $res;
    }
    
    private function tableExist(){
        $sql   = "show tables like '{$this->_options['table']}'";
        $list  = $this->_db->prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
        return count($list) > 0;
    }
}