## 工具类Utils使用说明
工具类Utils封装一些常用方法,方便开发任务时使用。

## 工具类Utils内置方法列表
特殊字符串转义:Utils::replace_keyword()<br>
引用php文件:Utils::loadphp()<br>
获取时间是星期几:Utils::getWeek()<br>
写日志:Utils::Log()<br>
设置和获取统计数据:Utils::counter()<br>
记录和统计时间（微秒）和内存使用情况:Utils::statistics()<br>
缓存管理:Utils::cache()<br>
获取数据库连接对象:Utils::db()<br>

## 方法详细说明

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
### 引用文件  Utils::loadphp($path) 
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
	    Utils::loadphp("tasks.backup.extend.PHPMailer.PHPMailerAutoload");
	}
}
?>
```
