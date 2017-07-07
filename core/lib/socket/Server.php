<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
namespace core\lib\socket;
use core\lib\Exception;
/**
 * socket服务端
 * @author cqcqphper 小草<cqcqphper@163.com>
 */
class Server {
    /**
     * @var Resource
     */
    public $_listenFD = null;

    /**
     * @var Resource
     */
    public $_connectFD = null;

    /**
     * Host
     * @var String
     */
    private $_host = null;

    /**
     * Port
     * @var Integer
     */
    private $_port = null;

    /**
     * Socket constructor.
     * @param $host 监听的IP
     * @param $port 监听的PORT
     */
    public function __construct($host, $port){
        $this->_host = $host;
        $this->_port = $port;
    }

    /**
     * 创建一个监听 SOCKET
     */
    public function listen(){
        if (!($this->_listenFD = socket_create(AF_INET, SOCK_STREAM, SOL_TCP))) {
            throw new Exception("socket_create err:".socket_strerror(socket_last_error()));
        }
        //在修改源码后重启启动总是提示bind: Address already in use,使用tcpreuse解决
        if (!socket_set_option($this->_listenFD, SOL_SOCKET, SO_REUSEADDR, 1)) {
            throw new Exception("socket_set_option err:".socket_strerror(socket_last_error()));
        }
        //阻塞模式
        if (!socket_set_block($this->_listenFD)) {
            throw new Exception("socket_set_block err:".socket_strerror(socket_last_error()));
        }
        //绑定到socket端口
        if (!socket_bind($this->_listenFD, $this->_host, $this->_port)) {
            throw new Exception("socket_bind err:".socket_strerror(socket_last_error()));
        }
        //开始监听
        if (!socket_listen($this->_listenFD, 5)) {
            throw new Exception("socket_listen err:".socket_strerror(socket_last_error()));
        }
    }

    /**
     *接收一个client的连接,生成一个新的连接描述符
     */
    public function accept(){
        if (!$this->_listenFD) {
            \core\lib\Ui::displayUI('no socket handler for accept.',false);
            return false;
        }
        //它接收连接请求并调用一个子连接Socket来处理客户端和服务器间的信息
        if (!($this->_connectFD = socket_accept($this->_listenFD))) {
            \core\lib\Ui::displayUI("socket_accept err:".socket_strerror(socket_last_error()),false);
            return false;
        }
    }

    /**
     * 从socket中读取一行数据
     * @return string
     */
    public function readLine(){
        if (!$this->_connectFD) {
            \core\lib\Ui::displayUI('no connfd for write.',false);
            return false;
        }
        /* PHP_NORMAL_READ碰到\r,\n,\0就停止 */
        $buf = trim(socket_read($this->_connectFD, 1024, PHP_NORMAL_READ)); //trim去掉末尾的\r
        /* 读取\n */
        socket_read($this->_connectFD, 1);
        return $buf;
    }

    /**
     * 从socket中读取指定字节的数据
     * @param $bytes
     * @return string
     */
    public function read($bytes){
        if (!$this->_connectFD) {
            \core\lib\Ui::displayUI('no connfd for write.',false);
            return false;
        }
        return socket_read($this->_connectFD, $bytes);
    }

    /**
     * @param $data
     */
    public function write($data){
        if (!$this->_connectFD) {
            \core\lib\Ui::displayUI('no connfd for write.',false);
        }
        if (!socket_write($this->_connectFD, $data, strlen($data))) {
            \core\lib\Ui::displayUI("socket_write err:".socket_strerror(socket_last_error()),false);
        }
    }

    public function closeListenFD(){
        if ($this->_listenFD) {
            socket_close($this->_listenFD);
        }
    }

    public function closeConnectFD(){
        if ($this->_connectFD) {
            socket_close($this->_connectFD);
        }
    }
}