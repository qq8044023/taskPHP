## 工具类Utils使用说明
工具类Utils封装一些常用方法,方便开发任务时使用。

## 工具类Utils内置方法列表
特殊字符串转义:Utils::replace_keyword()<br>
引用php文件:Utils::loadphp()<br>
获取时间是星期几:Utils::getWeek()<br>
获取配置参数:Utils::config()<br>
写日志:Utils::Log()<br>
设置和获取统计数据:Utils::counter()<br>
记录和统计时间（微秒）和内存使用情况:Utils::statistics()<br>
缓存管理:Utils::cache()<br>
获取数据库操作对象:Utils::db()<br>

## 详细使用说明
```php
use core\lib\Utils;
```
1. 特殊字符串转义:Utils::replace_keyword(): 
```php
    $str="Fds2334k345@";
    $res=Utils::replace_keyword($str);
    //输出  Fds2334k345\@
```

2. 引用文件  Utils::loadphp()
```php
	//引用插件
	Utils::loadphp("tasks.backup.extend.PHPMailer.PHPMailerAutoload");
```

3. 获取时间是星期几  Utils::getWeek()
```php
	Utils::getWeek('2016-11-11',true)
```

4. 获取配置参数  Utils::config()
```php
	//获取demo任务下的DB配置
	$db_config=Utils::config('DB','demo');
```

5. 写日志  Utils::Log()
```php
	//写字符串
	Utils::Log('hello taskPHP');
	//写数组
	$array=array('a','b');
	Utils::Log($array);
	//写对象
	$object=new xxx();
	Utils::Log($object);
```

6. 设置和获取统计数据  Utils::counter()
```php
	// 记录数据库操作次数
	Utils::counter('db',1); 
	// 记录读取次数
    Utils::counter('read',1); 
    // 获取当前页面数据库的所有操作次数
    echo Utils::counter('db');
    // 获取当前页面读取次数
    echo Utils::counter('read'); 
```

7. 记录和统计时间（微秒）和内存使用情况  Utils::statistics()
```php
	// 记录开始标记位
	Utils::statistics('begin'); 
    ...区间运行代码
    
    // 记录结束标签位
    Utils::statistics('end'); 
    
    // 统计区间运行时间 精确到小数后6位
    echo Utils::statistics('begin','end',6); 
    
    // 统计区间内存使用情况
    echo Utils::statistics('begin','end','m'); 
    
    //如果end标记位没有定义，则会自动以当前作为标记位
    //其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
```

8. 缓存管理  Utils::cache()
```php
	//写缓存
	$demo_data=array('a','b');
	Utils::cache('demo_data',$demo_data);
	//读缓存
	Utils::cache('demo_data');
	//删除缓存
	Utils::cache('demo_data',null);
```

9. 获取数据库操作对象  Utils::db()
```php
	//获取demo任务下的DB配置
	$db_config=Utils::config('DB','demo');
	//获取数据库操作对象
	$db=Utils::db($db_config);
```
