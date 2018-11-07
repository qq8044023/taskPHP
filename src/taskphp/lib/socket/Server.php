<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
namespace taskphp\socket;
/**
 * socket服务端
 * @author cqcqphper 小草<cqcqphper@163.com>
 */
class Server {
    /**
	 * 
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
        if(!extension_loaded('sockets')){
            \taskphp\Console::log('ERROR:sockets module has not been opened');die;
        }
        $this->_host = $host;
        $this->_port = $port;
    }

    /**
     * 创建一个监听 SOCKET
     */
    public function listen(){
        if (!($this->_listenFD = socket_create(AF_INET, SOCK_STREAM, SOL_TCP))) {
            \taskphp\Console::log("socket_create err:".socket_strerror(socket_last_error()));
        }
        //在修改源码后重启启动总是提示bind: Address already in use,使用tcpreuse解决
        if (!socket_set_option($this->_listenFD, SOL_SOCKET, SO_REUSEADDR, 1)) {
            \taskphp\Console::log("socket_set_option err:".socket_strerror(socket_last_error()));
        }
        //阻塞模式
        if (!socket_set_block($this->_listenFD)) {
            \taskphp\Console::log("socket_set_block err:".socket_strerror(socket_last_error()));
        }
        //绑定到socket端口
        if (!socket_bind($this->_listenFD, $this->_host, $this->_port)) {
            \taskphp\Console::log("socket_bind err:".socket_strerror(socket_last_error()));
        }
        //开始监听
        if (!socket_listen($this->_listenFD, 5)) {
            \taskphp\Console::log("socket_listen err:".socket_strerror(socket_last_error()));
        }
    }

    /**
     *接收一个client的连接,生成一个新的连接描述符
     */
    public function accept(){
        if (!$this->_listenFD) {
            \taskphp\Console::log('no socket handler for accept.');
            return false;
        }
        //它接收连接请求并调用一个子连接Socket来处理客户端和服务器间的信息
        if (!($this->_connectFD = socket_accept($this->_listenFD))) {
            \taskphp\Console::log("socket_accept err:".socket_strerror(socket_last_error()));
            return false;
        }
		return $this->_connectFD;
    }

    /**
     * 从socket中读取一行数据
     * @return string
     */
    public function readLine($connect){
        if (!$connect) {
            \taskphp\Console::log('no connfd for write.');
            return false;
        }
        /* PHP_NORMAL_READ碰到\r,\n,\0就停止 */
        $buf = trim(socket_read($connect, 1024, PHP_NORMAL_READ)); //trim去掉末尾的\r
        /* 读取\n */
        socket_read($connect, 1);
        return $buf;
    }

    /**
     * 从socket中读取指定字节的数据
     * @param $bytes
     * @return string
     */
    public function read($connect,$bytes){
        if (!$connect) {
            \taskphp\Console::log('no connfd for write.');
            return false;
        }
        return socket_read($connect, $bytes);
    }

    /**
     * @param $data
     */
    public function write($connect,$data){
        if (!$connect) {
            \taskphp\Console::log('no connfd for write.');
        }
        if (!socket_write($connect, $data, strlen($data))) {
            \taskphp\Console::log("socket_write err:".socket_strerror(socket_last_error()));
        }
    }

    public function closeListenFD(){
        if ($this->_listenFD) {
            socket_close($this->_listenFD);
        }
    }

    public function closeConnectFD($connect){
        if ($connect) {
            socket_close($connect);
        }
    }
}