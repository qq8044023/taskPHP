<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\Config;
use core\lib\Db;
/**
 * 测试任务
 */
class demoTask extends Task{
	public function run(){
	    $db=Db::setConfig(Config::get('DB'));
        $res=$db->table("表名")->model()->select("id")->from("表名")->row();
        var_dump($res);
	}
}
