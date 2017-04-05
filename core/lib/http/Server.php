<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/TimePhp
 */
namespace core\lib\http;
use core\lib\http\Basic;

class Server extends Basic{
    protected $www_root='';
    function __construct($www_root='',$address='0.0.0.0',$port=8002){
        parent::__construct(array(
            'addr'=>$address,
            'port' => $port,
        ));
        $this->www_root=$www_root;
    }

    function route_request($request){
        $uri = $request->uri;
        $doc_root = $this->www_root;
        if (preg_match('#/$#', $uri)){
            $uri .= "index.php";
        }
        
        if (preg_match('#\.php$#', $uri)){
            return $this->get_php_response($request, "$doc_root$uri");
        }else{
            return $this->get_static_response($request, "$doc_root$uri");
        }                
    }
    
    public function run(){
        echo 'Httpserver start...'.PHP_EOL;
        $this->run_forever();
    }
}