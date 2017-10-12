<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
namespace core\lib;
use core\lib\Worker;
use core\lib\WorkerExe;
use core\lib\WorkerRun;
use core\lib\Timer;
use core\lib\Task;
/**
 * 任务管理类
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class TaskManage{
    
	public static $_sleep="task_sleep";
	
	public static $_workerPrefix="task_worker_";
	
	public static $_workerList="task_list";
	
	/**
	 * 首次加载任务到内存
	 */
	public function load_worker(){
	    $task_list=Config::get('task_list');
	    if(!$task_list)echo "taskPHP Warning:No task at present".PHP_EOL;
	    foreach ($task_list as $key=>$value){
	        $class_name='tasks\\'.$key.'\\'.$key.'Task';
	        //设置任务
	        $worker= new Worker($key,new $class_name());
	        if(is_string($value['timer'])){
	            $timer = Timer::string_to_timer($value['timer']);
	        }
	        $worker->set_timer($timer);
	        $this->set_worker($worker);
	    }
	}
	/**
	 * 设置一个任务
	 * @param Worker $worker
	 * @param string $overwrite
	 * @return boolean
	 */
	public function set_worker(Worker $worker,$overwrite=true){
	    $timer=$worker->get_timer();
		$next_run_time=Timer::get_next_run_time(null,$timer);
		if ($next_run_time===false)$next_run_time=time();
		$timer=$worker->get_timer();
		$task=$worker->get_worker();
		
		$workerlist= (array) Utils::cache(static::$_workerList);
		if (!$overwrite && in_array($worker->get_name(),$workerlist)){
			//不重写
			return false;
		}
		$name=static::$_workerPrefix.$worker->get_name();
		$item=[];
		$item['timer']=$timer;
		$item['task']=$task;
		$item['skip']=intval($worker->get_skip());
		$item['run_time']=$next_run_time;
		Utils::cache($name,$item);
		if(!in_array($worker->get_name(),$workerlist)){
		    //添加一个任务
		    \core\lib\queue\Queue::push(static::$_workerList,$worker->get_name());
		    $this->un_sleep();
		}
		return true;
	}
	/**
	 * 运行任务
	 * @param Task|string $task
	 * @return boolean
	 */
	public function run_task($task){
	    if(!($task instanceof Task)){
	        return false;
	    }
		try{
		    ob_start();
		    $task->main();
		    $data=ob_get_contents();
		    ob_end_clean();
            Utils::log(get_class($task).'run success');
		}catch(Exception $e){
		    Utils::log([$task,$e->getMessage()],-1);
		}
		unset($data);
	}
	
	
	/**
	 * 把指定任务修改到下一个执行时间
	 * @param Worker $worker
	 * @param unknown $next_run_time
	 * @return boolean
	 */
	public function next_time_worker(Worker $worker,$next_run_time){
		if ($next_run_time===false){
			$this->del_worker($worker->get_name());
			return false;
		}
		$name=static::$_workerPrefix.$worker->get_name();
		$item=Utils::cache($name);
		$item['run_time']=$next_run_time;
		Utils::cache($name, $item);
		return true;
	}
	/**
	 * 执行一个暂停
	 * @param int $time
	 * @return \core\lib\TaskManage
	 */
	public function on_sleep($time){
		$time=intval($time);
		$time=$time<=0?0:$time;
		\core\lib\queue\Queue::pop(static::$_sleep,$time);
		return $this;
	}
	/**
	 * 解除一个暂停
	 * @return \core\lib\TaskManage
	 */
	public function un_sleep(){
		\core\lib\queue\Queue::push(static::$_sleep,'0');//
		return $this;
	}
	/**
	 * 删除一个任务
	 * @param string $worker_name
	 * @return boolean
	 */
	public function del_worker($worker_name){
		$name=static::$_workerPrefix.$worker_name;
		Utils::cache($name,null);
		$re=\core\lib\queue\Queue::srem(static::$_workerList,$worker_name);
		if(!$re) return false;
		$this->un_sleep();
		return true;
	}
	/**
	 * 获取待执行任务列表
	 * @return WorkerRun[]
	 */
	public function run_worker_list(){
		$list=(array) Utils::cache(static::$_workerList);	
		$out=[];
		foreach ($list as $value){
			$name=static::$_workerPrefix.$value;
			$item=Utils::cache($name);
			$task=$item['task'];
			$timer=$item['timer'];
			$run_time=intval($item['run_time']);
			if ((!$timer instanceof Timer)||$run_time<=0)continue;
			$worker=new Worker($value, $task);
			$worker->set_timer($timer);
			$worker->set_skip($item['skip']);
			$out[]=new WorkerRun($worker, $run_time);
		}
		return $out;
	}
	/**
	 * 派发指定任务
	 * @param Worker $worker
	 */
	public function exec_worker(Worker $worker){
		return WorkerExe::instance()->exec($worker);
	}
}