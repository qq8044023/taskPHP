# 如何在thinkphp5中使用taskPHP框架


## 自定义命令行

> 对自定义命令行感兴趣的可以去看 [thinkphp5官方手册](https://www.kancloud.cn/manual/thinkphp5/235129)


第一步,安装最新的taskPHP
```
composer require taskphp/taskphp dev-master
```
如果下载不下来,可以尝试修改composer镜像地址:
``` php

composer config -g repo.packagist composer https://packagist.phpcomposer.com

```

第二步, 配置TP5项目的 `application/command.php` 文件

```php
<?php
return [
    'app\index\command\Taskphp',
];
```

第三步, 创建Taskphp命令文件  `application/index/command/Taskphp.php` 

```php
<?php
namespace app\index\command;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\console\input\Option;

// 载入taskphp入口文件
require_once dirname(APP_PATH).'/vendor/taskphp/taskphp/src/taskphp/base.php';

class Taskphp extends Command{
	
	protected function get_config(){
		return [
			//任务列表
			'task_list'=>[
				//key为任务名，多任务下名称必须唯一
				'demo'=>[
					'callback'=>['app\\index\\command\\Demo','run'],//任务调用:类名和方法
					//指定任务进程最大内存  系统默认为512M
					'worker_memory'      =>'1024M',
					//开启任务进程的多线程模式
					'worker_pthreads'   =>false,
					//任务的进程数 系统默认1
					'worker_count'=>1,
					//crontad格式 :秒 分 时 天 月 年 周
					'crontab'     =>'/5 * * * * * *',
				],
			],	
		];
	}
    protected function configure(){
        $this->addArgument('param', Argument::OPTIONAL);
        // 设置命令名称
        $this->setName($_SERVER['argv'][1])->setDescription('this is a taskphp!');
    }
	
    protected function execute(Input $input, Output $output){
		//系统配置
		$config= $this->get_config();
		//加载配置信息
		\taskphp\Config::load($config);
		//定义启动文件入口标记
		define("START_PATH", dirname(APP_PATH));
		//运行框架
		\taskphp\App::run();
    }
}
```

第四步, 创建Demo任务文件  `application/index/command/Demo.php` 
```php
namespace app\index\command;
use taskphp\Utils;
/**
 * 测试任务 
 */
class Demo{
    /**
     * demo任务入口
     */
	public static function run(){
	    Utils::log('demo1任务运行成功'); 
	    //可以调用thinkphp内的json函数
	    //Utils::log(json(['message'=>'hello taskphp'])); 
	}
}

```

## 大功告成,开始使用

### 运行 (`进入tp5根目录`)

```
php think start
```

命令列表

> start [all|任务名]  启动 可不带参数默认all


> close all 结束框架  必带参数all


> 如果需要完整的整合demo源码 加群taskPHP ①群:375841535（空）群共享里面获取。

