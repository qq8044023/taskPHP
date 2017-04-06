<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\db;
/**
 * 测试任务
 */
class demoTask extends Task{
    
	public function run(){
	    
	    $db=Db::setConfig();
	    
	    
	    $str="测试任务demoTask->run方法运行成功 \n";
		echo $str;
		\core\lib\Log::input($str);
		flush();
	}
}
