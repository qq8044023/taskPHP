<?php

namespace taskphp\socket;
use taskphp\Exception;
/**
 * socket客户端类
 * 
 * @author cqcqphper <cqcqphper@163.com>
 **/
class Client{
    /**
     * 配置信息
     * @var array
     */
    protected $_config = array(
        'persistent' => false, //是否长连接
        'host' => '127.0.0.1', //ip
        'protocol' => 'tcp', //协议
        'port' => 19000,//端口
        'timeout' => 30 //超时设置
    );
    /**
     * 连接句柄
     * @var unknown
     */
    public $connection = null;
    /**
     * 连接状态
     * @var unknown
     */
    public $connected = false;
    /**
     * 错误信息
     * @var unknown
     */
    public $error = array();
    /**
     * 初始化配置
     * @param array $config
     */
    public function __construct($config = array()) {
        $this->_config = array_merge($this->_config,$config);
    }
    /**
     * 连接到服务器
     */
    public function connect() {
        if ($this->connection != null) {
            $this->disconnect();
        }
        if ($this->_config['persistent'] == true) {
            if($this->_config['protocol'] == 'tcp'){
                $this->connection = @pfsockopen($this->_config['host'], $this->_config['port'], $errNum, $errStr, $this->_config['timeout']);
            }else{
                $this->connection = @pfsockopen("udp://".$this->config['host'], $this->config['port'], $errNum, $errStr, $this->_config['timeout']);
            }
        } else {
            if($this->_config['protocol'] == 'udp'){
                $this->connection = @fsockopen("udp://".$this->_config['host'], $this->_config['port'], $errNum, $errStr, $this->_config['timeout']);
            }else{
                $this->connection = @fsockopen($this->_config['host'], $this->_config['port'], $errNum, $errStr, $this->_config['timeout']);
            }
        }
        if (!empty($errNum) || !empty($errStr)) {
            throw new Exception($errStr,$errNum);
        }
        $this->connected = is_resource($this->connection);
        return $this->connected;
    }
    /**
     * 写字符串数据
     * @param string $data
     */
    public function write($data) {
        if (!$this->connected) {
            if (!$this->connect()) {
                return false;
            }
        }
        return fwrite($this->connection, $data, strlen($data));
    }
    /**
     * 写字节数据
     * @param string $data 数据
     * @param int $len 长度
     * @return boolean|number
     */
    public function writeByte($data, $len) {
        if (!$this->connected) {
            if (!$this->connect()) {
                return false;
            }
        }
        return fwrite($this->connection, $data, $len);
    }
    /**
     * 读数据
     * @param number $length 默认1024字节
     */
    public function read($length=1024) {
        if (!$this->connected) {
            if (!$this->connect()) {
                return false;
            }
        }
        if (!feof($this->connection)) {
            return fread($this->connection, $length);
        } else {
            return false;
        }
        //$this->disconnect();
    }
    /**
     * 关闭连接
     */
    public function disconnect() {
        if (!is_resource($this->connection)) {
            $this->connected = false;
            return true;
        }
        $this->connected = !fclose($this->connection);
        if (!$this->connected) {
            $this->connection = null;
        }
        return !$this->connected;
    }
    /**
     * 析构
     */
    public function __destruct() {
        $this->disconnect();
    }
}