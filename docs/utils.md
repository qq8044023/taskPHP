## 系统封装的操作函数指南
Utils::replace_keyword(),Utils::import(),Utils::getWeek(),Utils::writeLog()

### 特殊字符串转义  Utils::replace_keyword($string,$exclude_str) 
$string		必填	需要处理的字符串<br>
$exclude_str	可为空	自定义特殊字符串<br>
``` php
<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\Utils;
/**
 * 测试任务
 */
class demoTask extends Task{
	public function run(){
	    $str="Fds2334k345@";
	    $res=Utils::replace_keyword($str);
	    //输出  Fds2334k345\@
	}
}
?>
```
### 引用文件  Utils::import($path) 
$path		必填	需要引用的php文件<br>
``` php
<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\Utils;
/**
 * 测试任务
 */
class demoTask extends Task{
	public function run(){
	    //引用插件
	    Utils::import("tasks@backup@extend@PHPMailer@PHPMailerAutoload");
	}
}
?>
```
### 打印输出到日志文件  Utils::writeLog($arr) 
$arr		必填	需要打印的数组<br>
$show		可为空	false 	var_dump	true	print_r<br>
$logPath	可为空	日志文件存放目录<br>
``` php
<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\Utils;
/**
 * 测试任务
 */
class demoTask extends Task{
	public function run(){
	    //会把你要打印的数据写入到log.txt文件
	    Utils::writeLog(array('a','b'));
	}
}
?>
```
