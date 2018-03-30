## 工具类Utils使用说明
工具类Utils封装一些常用方法,方便开发任务时使用。

我们没有把所有的方法都列出来，需要了解其它方法请查看Utils工具类的注释。

## 部分方法详细使用说明
```php
use taskphp\Utils;
```

获取配置参数  Utils::config()
```php
	//获取db配置
	$db_config=Utils::config('db');
```

写日志  Utils::Log()
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

设置和获取统计数据  Utils::counter()
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

记录和统计时间（微秒）和内存使用情况  Utils::statistics()
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

缓存管理  Utils::cache()
```php
	//写缓存
	$demo_data=array('a','b');
	Utils::cache('demo_data',$demo_data);
	//读缓存
	Utils::cache('demo_data');
	//删除缓存
	Utils::cache('demo_data',null);
```

获取数据库操作对象  Utils::db()
```php
	//获取数据库操作对象
	$tablename='test';//表名
	$db=Utils::db($tablename);
```
