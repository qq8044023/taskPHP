## 整合说明
1. 下载taskphp。
``` php

composer require taskphp/taskphp

```
如果下载不下来,可以尝试修改composer镜像地址:
``` php

composer config -g repo.packagist composer https://packagist.phpcomposer.com

```

2. 在\application\index\command创建文件Taskphp.php。

``` php

<?php
namespace app\index\command;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\console\input\Option;
//用户目录
//define("TASKS_PATH", dirname(APP_PATH).'/tasks');
// 载入taskphp入口文件
require_once dirname(APP_PATH).'/vendor/taskphp/taskphp/src/taskphp/base.php';
class Taskphp extends Command{

	protected function get_config(){
		return [
			//系统队列配置
			'queue'=>[
				'drive'         => 'Sqlite',//驱动类型 Sqlite|Redis|Mysql|Shm
				''
			],
			//系统日志配置
		   'log'=>[
			   //日志目录
			   'path'=>dirname(APP_PATH).DS.'runtime',
			   //错误日志开关 true开启  false关闭
			   'error'=>true,
			   //调试日志开关 true开启  false关闭
			   'debug'=>true,
		   ],
			//web管理配置
			//内网访问地址：http://127.0.0.1:8082
			//外网访问地址：http://ip:8082
			'web_manage'=>[
				//ip地址
				'address'=>'0.0.0.0',
				//端口
				'port'=>8082,
			],
			//任务列表
			'task_list'=>[
				'app\\index\\command\\Demo'=>[
					//指定任务进程最大内存  系统默认为512M
					'worker_memory'      =>'1024M',
					//开启任务进程的多线程模式
					'worker_pthreads'   =>false,
					//任务的进程数 系统默认1
					'worker_count'=>1,
					//crontad格式 :秒 分 时 天 月 年 周
					'timer'     =>'/5 * * * * * *',
				],
			],
			'db'=>[
				'type'          =>  'mysql',
				'username'      =>  'root',
				'password'      =>  'root',
				'host'      =>  '127.0.0.1',
				'port'      =>  '3306',
				'name'      =>  'test',
				// 数据库编码默认采用utf8
				'charset'       => 'utf8',
				// 数据库表前缀
				'prefix'        => 'test_',
				// 开启断线重连
				'break_reconnect'=>true,
			],
			
		];
	}
    protected function configure(){
        $this->addArgument('param', Argument::OPTIONAL);//查看状态
        // 设置命令名称
        $this->setName($_SERVER['argv'][1])->setDescription('this is a supercron!');
    }
	
    protected function execute(Input $input, Output $output){
		//系统配置
		$config= $this->get_config();
		//加载配置信息
		\taskphp\Config::load($config);
		//定义入口标记
		define("START_PATH", dirname(APP_PATH));
		//运行框架
		\taskphp\App::run();
    }
}

```

3.修改application下的command.php文件。

``` php
return [
	'app\index\command\Taskphp',
];

``` 
4.在\application\index\command创建文件Demo.php。

``` php
<?php
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
	}
}


```

5.启动任务 php think taskphp start 如果需要完整的整合demo源码 加群taskPHP ①群:375841535（空）群共享里面获取。

