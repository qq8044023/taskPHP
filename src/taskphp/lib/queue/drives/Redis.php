<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
namespace taskphp\queue\drives;

/**
 * 队列驱动-Redis
 *
 * 说明 :
 * 1 使用redis的list作为队列的中间件，不支持ack
 *
 * 使用场景:
 * 1 环境中有redis，业务不需要ack
 */
class Redis{

    /**
     * 设置属性
     * @var array
     */
    private  $_options=[
        'prefix'=>'queue',
        'host'=>'127.0.0.1',
        'port'=>'6379',
        'password'   => '',
    ];

    /**
     * @var \Redis
     */
    private $redis;
    /**
     * RedisQueue constructor.
     * @param string $queue_name
     * @param array $config
     */
    public function __construct(array $options = []){
        if(!extension_loaded('redis')){
            \taskphp\Console::log('ERROR:redis module has not been opened');die;
        }
        $this->_options = array_merge($this->_options,$options);
        $this->redis = new \Redis();
        $this->redis->connect($this->_options['host'],$this->_options['port']);
        if ('' != $this->_options['password']) {
            $this->redis->auth($this->_options['password']);
        }
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name = false) {
        $value    = $this->redis->get($this->_options['prefix'] . $name);
        $value=unserialize($value);
        return $value;
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @return boolen
     */
    public function set($name, $value) {
        $name = $this->_options['prefix'] . $name;
        $value=serialize($value);
        $result = $this->redis->set($name, $value);
        return $result;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function rm($name) {
        return $this->redis->delete($this->_options['prefix'] . $name);
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
        return $this->redis->flushDB();
    }
}