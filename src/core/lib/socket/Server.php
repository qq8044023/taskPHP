<?php
/**
 * TODO 关闭socket时需要关闭该进程
 * socket (new MySocketServer())->run();
 * @author 码农<8044023@qq.com>
 **/
namespace core\lib\socket;
use core\lib\stream\Handshake;
class Server{
    use Helper;
    protected $socket;
    protected $clients = [];
    protected $changed;
    public static $_clients=[];
    public function __construct($port = 19000){
        set_time_limit(0);
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

        //bind socket to specified host
        socket_bind($socket, 0, $port);
        //listen to port
        socket_listen($socket);
        $this->socket = $socket;
    }

    public function __destruct(){
        foreach($this->clients as $client) {
            socket_close($client);
        }
        socket_close($this->socket);
    }

    public function run(){
        echo 'Socketserver start...'.PHP_EOL;
        while(true) {
            $this->waitForChange();
            $this->checkNewClients();
            $this->checkMessageRecieved();
            $this->checkDisconnect();
        }
    }

    public function checkDisconnect(){
        foreach ($this->changed as $changed_socket) {
            $buf = @socket_read($changed_socket, Handshake::$_structLength, PHP_NORMAL_READ);
            if ($buf !== false) { // check disconnected client
                continue;
            }
            // remove client for $clients array
            $found_socket = array_search($changed_socket, $this->clients);
            socket_getpeername($changed_socket, $ip);
            unset($this->clients[$found_socket]);
            //$response = 'client ' . $ip . ' has disconnected';
            //$this->sendMessage($response);
            call_user_func_array($this->onClose,array('客户端编号'));
        }
    }

    public function checkMessageRecieved(){
        self::$_clients=$this->changed;
        foreach ($this->changed as $key => $socket) {
            $buffer = null;
            while(@socket_recv($socket, $buffer, Handshake::$_structLength, 0) >= 1) {
               // $this->sendMessage($Gateway->onMessage(trim($buffer)) . PHP_EOL);
                //Gateway::onMessage($socket,$buffer);
                unset($this->changed[$key]);
                //$buffer=Handshake::set($buffer);
                call_user_func_array($this->onMessage,array($socket,$buffer));
                break;
            }
        }
    }

    public function waitForChange(){
        //reset changed
        $this->changed = array_merge([$this->socket], $this->clients);
        //variable call time pass by reference req of socket_select
        $null = null;
        //this next part is blocking so that we dont run away with cpu
        socket_select($this->changed, $null, $null, null);
    }

    public function checkNewClients(){
        if (!in_array($this->socket, $this->changed)) {
            return; //no new clients
        }
        $socket_new = socket_accept($this->socket); //accept new socket
        $first_line = socket_read($socket_new, Handshake::$_structLength);
        //$this->sendMessage('a new client has connected' . PHP_EOL);
        //$this->sendMessage('the new client says ' . trim($first_line) . PHP_EOL);
        $this->clients[] = $socket_new;
        unset($this->changed[0]);
        //新用户连接时触发的回调
        call_user_func_array($this->onConnect,array($socket_new));
    }
    /**
     * 给全部用户推送消息
     * @param unknown $msg
     * @return  string
     *   */    
    public function sendMessage($data){
        //$data=Handshake::set($data);
        foreach($this->clients as $client){
            @socket_write($client,$data,strlen($data));
        }
        return true;
    }
    /**
     * 给单用户推送消息
     * @param   $client_id  绑定的用户id  
     * @return bool
     *   */
    public static function sendToClient($client_id,$data){
        //$data=Handshake::set($data);
        @socket_write($client_id,$data,strlen($data));
        return true;
    }
    /**
     * 给所有用户推送消息
     * @param unknown $data
     * @return bool
     *   */
    public static function sendToAll($data){
        //$data=Handshake::set($data);
        foreach(self::$_clients as $client){
            @socket_write($client,$data,strlen($data));
        }
        return true;
    }
    /**
     * 关闭 某个连接
     * @param unknown $client_id  */
    public static function onClose($client_id){
        socket_close($client_id);
    }
}