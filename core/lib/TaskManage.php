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
	
	public static $_workerPrefix="task_worker:";
	
	public static $_workerList="task_list";
	
	public static $_workerLoglist="task_loglist";
	
	/**
	 * 首次加载任务到内存
	 */
	public function load_worker(){
	    $task_list=Config::get('task_list');
	    if(!$task_list)echo "taskPHP Warning:No task at present".PHP_EOL;
	    foreach ($task_list as $key=>$value){
	        $class_name=$key;
	        if(isset($value['class_name']) && empty($value['class_name'])){
	            if($value['class_name']===true){
	                $class_name='tasks\\'.$key.'\\'.$key.'Task';
	            }
	        }else{
	            $class_name='tasks\\'.$key.'\\'.$key.'Task';
	        }
	        
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
		
		$workerlist= (array) Queue::get(static::$_workerList);
		if (!$overwrite && in_array($worker->get_name(),$workerlist)){
			//不重写
			return false;
		}
		$name=static::$_workerPrefix.$worker->get_name();
		$item=array();
		$item['timer']=$timer;
		$item['task']=$task;
		$item['skip']=intval($worker->get_skip());
		$item['run_time']=$next_run_time;
		Queue::set($name,$item);
		if(!in_array($worker->get_name(),$workerlist)){
		    //添加一个任务
		    Queue::lPush(static::$_workerList,$worker->get_name());
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
	    if(is_string($task)){
	        $task_list=Config::get('task_list');
	        if(!isset($task_list[$task]))return false;
	        $class_name=$task;
	        if(isset($task_list[$task]['class_name']) && $task_list[$task]['class_name']===true){
	            $class_name='tasks\\'.$task.'\\'.$task.'Task';
	        }elseif(empty($task_list[$task]['class_name'])){
	            $class_name='tasks\\'.$task.'\\'.$task.'Task';
	        }
	        $task=new $class_name();
	    }elseif (($task instanceof Task)){
	        $task;
		}else{
		    return false;
		}
		try{
		    ob_start();
		    $task->main();
		    $data=ob_get_contents();
		    ob_end_clean();
            $this->add_log(array(get_class($task),$data?$data:'run success'));
		}catch(Exception $e){
		    Utils::log(array($task,$e->getMessage()),-1);
		    $this->add_log(array(get_class($task),$data?$data:'run fail'));
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
		$item=Queue::get($name);
		$item['run_time']=$next_run_time;
		Queue::set($name, $item);
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
		Queue::brPop(static::$_sleep,$time);
		return $this;
	}
	/**
	 * 解除一个暂停
	 * @return \core\lib\TaskManage
	 */
	public function un_sleep(){
		Queue::lPush(static::$_sleep,'0');//
		return $this;
	}
	/**
	 * 删除一个任务
	 * @param string $worker_name
	 * @return boolean
	 */
	public function del_worker($worker_name){
		$name=static::$_workerPrefix.$worker_name;
		Queue::rm($name);
		$re=Queue::srem(static::$_workerList,$worker_name);
		if(!$re) return false;
		$this->un_sleep();
		return true;
	}
	/**
	 * 获取待执行任务列表
	 * @return WorkerRun[]
	 */
	public function run_worker_list(){
		$list=(array) Queue::get(static::$_workerList);	
		$out=array();
		foreach ($list as $value){
			$name=static::$_workerPrefix.$value;
			$item=Queue::get($name);
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
	/**
	 * 记录运行日志
	 * @param array $data
	 * 默认只保留最后100条结果
	 */
	public function  add_log(array $data){
	    array_unshift($data,date("Y-m-d H:i:s"));
	    //加入队列  记录日志
	    Queue::lPush(static::$_workerLoglist,$data);
	    $data= Utils::cache(static::$_workerLoglist);
	    if(is_array($data) && count($data)>100){
	        Queue::brPop(static::$_workerLoglist,1);
	    }
	}
	/**
	 * 获取任务执行结果列表
	 * 默认只保留最后100条结果
	 * @return array[
	 * 	'0'=>执行时间
	 * 	'1'=>执行对象
	 * 	'2'=>执行输出
	 * ]
	 */
	public function worker_result(){
		//获取最后几个运行结果
		$data= Utils::cache(static::$_workerLoglist);
		return $data; 
	}
}