<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\Utils;
use core\lib\http\Client;
use tasks\demo\lib\Demolib;
/**
 * 测试任务 
 */
class demoTask extends Task{
    /**
     * 任务入口
     * (non-PHPdoc)
     * @see \core\lib\Task::run()
     */
	public function run(){
	    
	    //加载demo任务下的lib类
	    $demolib_object = new Demolib();
	    $demolib_object->run();
	    
	    
	    
	    //远程采集测试
	    //http下的Client类的详细使用说明请参考类描述
	    $http = new Client();
	    $result =  $http->get('http://www.baidu.com');
	    $res='http fail';
	    if($result!='')$res='http success';
	    Utils::log($res);
	    
	    //数据库操作测试
	    //Config::get()说明：配置文件中配置数据库连接信息，第一个参数为配置项，第二个参数为作用域 demo 表示本任务（demo任务）下的配置文件
	    /* $db_config=Utils::config('DB','demo');
	    $db=Utils::db($db_config);
	    $res=$db->table("表名")->find();
	    var_dump($res); */
	    
	    
	    $str="demoTask run success";
	    //echo $str;
		Utils::log($str);
		flush();
	}
}
