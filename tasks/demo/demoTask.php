<?php
namespace tasks\demo;
use taskphp\Task;
use taskphp\Utils;
use taskphp\Config;
use tasks\demo\lib\Demolib;
use taskphp\Db;
/**
 * 测试任务 
 */
class demoTask{
    /**
     * 任务入口
     * (non-PHPdoc)
     * @see taskphp\Task::run()
     */
	public function run(){
	    //数据库操作 获取一条数据
	     /* $res=Utils::db('table1')->find();
	    Utils::log($res); */
	     
	    /* //方法二
	     $res=Utils::db()->table("table1")->where("id=1")->limit(2)->order("id DESC")->select();
	     Utils::log($res); */
	     
	    /* //方法三
	     $db=Db::connect();
	     $res=Utils::db()->table("user")->alias("a")->join("user_third AS b ON a.uid=b.uid","LEFT")->where("a.status=1")->limit(2)->order("a.uid DESC")->select();
	     Utils::log(); */
	     
	    //Utils::db()->table("user")->getSql()  打印sql语句
	     
	    /* //方法四
	     $res=Utils::db()->table("user_phone_log")->where(array("phone_log_id"=>2))->update(array("uid"=>3));
	     Utils::log($res); */
	     
	    //方法五
	    /* 
	     $res=Utils::db()->table("user_phone_log")->add(array(
	     "uid"         =>22,
	     "status"      =>1,
	     "create_date" =>time(),
	     "phone"       =>13111111
	     ));
	     Utils::log($res); */
	     
	    /* //方法六
	     $res=Utils::db()->table("user_phone_log")->where(array("uid"=>22))->delete();
	     Utils::log($res); */
	    
	    //加载demo任务下的lib类
	    $demolib_object = new Demolib();
	    $demolib_object->run();
	    Utils::log('demo任务运行成功'); 
	    
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
