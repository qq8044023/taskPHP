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
