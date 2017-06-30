<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
use core\lib\http\Server;
if(!defined('IS_CLI')){
    include_once __DIR__."/guide.php";
}
if(IS_CLI==false)die("plase run in cli".PHP_EOL);

$config=array(
    'address'=>'0.0.0.0',
    'port'=>8082,
);
//实例化服务器对象
//内网访问地址：http://127.0.0.1:8082
//外网访问地址：http://ip:8082
$web_server = new Server($config);
//运行服务器
$web_server->listen();

