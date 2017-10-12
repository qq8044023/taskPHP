## Mysql数据库操作

## 详细使用说明
```php
use core\lib\Utils;
```
1. 配置mysql 将以下配置代码加入到你的xxTask.php同级目录的config.php里面

``` php

<?php
return array(
    /**
     * 数据库配置
     *   */
    'DB'=>array(
        'DB_TYPE'   => "mysql", // 数据库类型
        'DB_HOST'   => "127.0.0.1", // 服务器地址
        'DB_NAME'   => "tourism_game", // 数据库名
        'DB_USER'   => "root", // 用户名
        'DB_PWD'    => "root",  // 密码
        'DB_PORT'   => 3306, // 端口
        'DB_PREFIX' => "tourism_", // 数据库表前缀
        'DB_PARAMS'=>array('persist'=>true),//是否支持长连接
    ),
);

```

2.数据库操作(数据库操作和ThinkPHP一样操作)

``` php

<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\Utils;
/**
 * 测试任务 
 * 村长<8044023@qq.com>
 */
class demoTask extends Task{
    /**
     * 任务入口
     * (non-PHPdoc)
     * @see \core\lib\Task::run()
     */
	public function run(){
	    //初始化 配置信息  demo任务下的DB配置
	    Utils::dbConfig(Utils::config('DB','demo'));
	    //初始化模型 和ThinkPHP 的M()方法 一样
	    $model=Utils::model("gameActivity");
	    //操作同ThinkPHP 操作
	    $res=$model->where(array("id"=>1))->order("id DESC")->limit(10)->select();
	    //写入日志
	    Utils::log($res);
	}
}

```



