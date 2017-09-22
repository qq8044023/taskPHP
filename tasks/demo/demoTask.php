<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\Utils;
use core\lib\Config;
use core\lib\http\Client;
use tasks\demo\lib\Demolib;
/**
 * 测试任务 
 */
class demoTask extends Task{
    public $_timer='/2 * * * * * *';
    /**
     * 任务入口
     * (non-PHPdoc)
     * @see \core\lib\Task::run()
     */
	public function run(){
	    
	    //加载demo任务下的lib类
	     $demolib_object = new Demolib();
	    $demolib_object->run();
	    Utils::log('demo任务运行成功');

	    /*
	    //远程采集测试
	    //http下的Client类的详细使用说明请参考类描述
	    $http = new Client();
	    $result =  $http->get('http://www.baidu.com');
	    $res='http fail';
	    if($result!='')$res='http success';
	    Utils::log($res); */
	    
	    //数据库操作测试
	    //Config::get()说明：配置文件中配置数据库连接信息，第一个参数为配置项，第二个参数为作用域 demo 表示本任务（demo任务）下的配置文件
	    /*  
	    Utils::dbConfig(Utils::config('DB','demo'));
	    $res=Utils::model("gameActivity")->find();
	    //echo $str;
	    Utils::log($res);
	    
		flush();*/
	    
	    /**测试动态修改配置**/
	    /* Utils::counter('run_count',1);
	    if(Utils::counter('run_count')==1){
	        $config=Config::get('task_list.demo');
	        Utils::log('修改前'.$config['timer']);
	    }elseif(Utils::counter('run_count')==2){
	        $arr=[
	            'timer'     =>'/10 * * * * * *',
	        ];
	        Config::set('task_list.demo',$arr);
	        $task_manage=new \core\lib\TaskManage();
	        $task_manage->load_worker();
	        
	        $config=Config::get('task_list.demo');
	        Utils::log('修改后'.$config['timer']);
	    } */
	    /**测试动态修改配置 **/
	    
	}
}
