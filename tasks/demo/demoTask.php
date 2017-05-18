<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\Utils;
use core\lib\http\Client;
use tasks\demo\lib\Demolib;
use core\lib\Config;
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
	    $config=Config::get('DB','demo');
	    $db=Utils::db($config);
	    $res=$db->table("vipqb_addons")->sum("id");
	    Utils::log($res);
	    //加载demo任务下的lib类
<<<<<<< HEAD
	   /*  $demolib_object = new Demolib();
=======
	    /* $demolib_object = new Demolib();
>>>>>>> 39f9ae4feef8a2f7c30ac898aca79b5ea9fc235d
	    $demolib_object->run();

	    //远程采集测试
	    //http下的Client类的详细使用说明请参考类描述
	    $http = new Client();
	    $result =  $http->get('http://www.baidu.com');
	    $res='http fail';
	    if($result!='')$res='http success';
<<<<<<< HEAD
	    Utils::log($res); */
	    
	    //数据库操作测试
	    //Config::get()说明：配置文件中配置数据库连接信息，第一个参数为配置项，第二个参数为作用域 demo 表示本任务（demo任务）下的配置文件
	    /*  
	    Utils::dbConfig(Utils::config('DB','demo'));
	    $res=Utils::model("gameActivity")->find();
	    //echo $str;
	    Utils::log($res);
	    */
		flush();
=======
	    Utils::log($res);
	     */
	    //数据库操作测试
	    //Config::get()说明：配置文件中配置数据库连接信息，第一个参数为配置项，第二个参数为作用域 demo 表示本任务（demo任务）下的配置文件
	    /* $db_config=Utils::config('DB','demo');
	    $db=Utils::db($db_config);
	    $res=$db->table("表名")->find();
	    var_dump($res); */
	    
	  /*   
	    $str="demoTask run success";
	    //echo $str;
		Utils::log($str);
		flush(); */
>>>>>>> 39f9ae4feef8a2f7c30ac898aca79b5ea9fc235d
	}
}
