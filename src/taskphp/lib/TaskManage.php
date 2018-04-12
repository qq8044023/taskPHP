<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
namespace taskphp;
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
	    if(!$task_list)Console::log('taskphp\TaskManage::load_worker task_list is empty');
	    foreach ($task_list as $key=>$value){
	        //设置任务
	        $worker= new Worker($key,$value);
	        if(is_string($value['crontab'])){
	            $crontab = Crontab::string_to_crontab($value['crontab']);
	        }
	        $worker->set_crontab($crontab);
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
	    $crontab=$worker->get_crontab();
		$next_run_time=Crontab::get_next_run_time(null,$crontab);
		if ($next_run_time===false)$next_run_time=time();
		$crontab=$worker->get_crontab();
		$task=$worker->get_worker();
		$workerlist= (array) Utils::cache(static::$_workerList);
		if (!$overwrite && in_array($worker->get_name(),$workerlist)){
			//不重写
			return false;
		}
		$name=static::$_workerPrefix.$worker->get_name();
		$item=[];
		$item['crontab']=$crontab;
		$item['task']=$task;
		$item['skip']=intval($worker->get_skip());
		$item['run_time']=$next_run_time;
		Utils::cache($name,$item);
		if(!in_array($worker->get_name(),$workerlist)){
		    //添加一个任务
		    Queue::push(static::$_workerList,$worker->get_name());
		    $this->un_sleep();
		}
		return true;
	}
	/**
	 * 运行任务
	 * @param array $callback
	 * @return boolean
	 */
	public function run_task($callback){
		try{
		    if(Utils::config('log')['debug']){
		        Utils::statistics('begin');
		        Utils::log($callback[0].'::'.$callback[1].' [--START--]');
		    }
		    ob_start();
		    call_user_func($callback);
		    $data=ob_get_contents();
		    ob_end_clean();
		    if(Utils::config('log')['debug']){
                Utils::statistics('end');
                Utils::log($callback[0].'::'.$callback[1].' [--END--][RunTime:'.Utils::statistics('begin','end',6).'s]');
            }
		}catch(Exception $e){
		    Utils::log([$callback[0].'::'.$callback[1],$e->getMessage()],-1);
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
	 * @return taskphp\TaskManage
	 */
	public function on_sleep($time){
		$time=intval($time);
		$time=$time<=0?0:$time;
		Queue::pop(static::$_sleep,$time);
		return $this;
	}
	/**
	 * 解除一个暂停
	 * @return taskphp\TaskManage
	 */
	public function un_sleep(){
		Queue::push(static::$_sleep,'0');//
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
		$list=(array) Utils::cache(static::$_workerList);	
		$out=[];
		foreach ($list as $value){
			$name=static::$_workerPrefix.$value;
			$item=Utils::cache($name);
			$task=$item['task'];
			$crontab=$item['crontab'];
			$run_time=intval($item['run_time']);
			if ((!$crontab instanceof Crontab)||$run_time<=0)continue;
			$worker=new Worker($value, $task);
			$worker->set_crontab($crontab);
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