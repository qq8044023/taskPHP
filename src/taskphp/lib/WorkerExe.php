<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
namespace taskphp;
/**
 * 任务执行类
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class WorkerExe{
	/**
	 * 任务KEY
	 * @var unknown
	 */
	public static $_worker_exec="task_exec_";

	private static $_workerExe;
	
	protected $_worker;
	
	public static function instance(){
		if (self::$_workerExe===null){
			self::$_workerExe= new self();
		}
		return self::$_workerExe;
	}
	/**
	 * 派发执行任务
	 * @param Worker $worker
	 */
	public function exec(Worker $worker){
	    if(Utils::cache('listen'.$worker->get_name())=='true'){
	        Queue::push(static::$_worker_exec.$worker->get_name(),$worker->get_worker());//加入队列
	    }
	}
	
	/**
	 * 任务进行监听
	 */
	public function listen($task_name){
	    $config=Utils::config('task_list.'.$task_name);
	    ini_set('memory_limit',$config['worker_memory']);
	    Utils::log('worker_listen daemon pid:'.getmypid().' Start');
		$taskManage=new TaskManage();
		register_shutdown_function([$this,'shutdown_function']);
		while (Utils::cache('listen'.$task_name)=='true'){
			$this->_worker=Queue::pop(static::$_worker_exec.$task_name);//取出队列
			if(!$this->_worker){
			    continue;
			}
			if(extension_loaded('pthreads') && $config['worker_pthreads']){//多线程模式
			    Pthread::call($taskManage,$config['callback']);
			}else{//单线程模式
			    $taskManage->run_task($config['callback']);
			}
		}
	}
	public function shutdown_function(){
	    Utils::log('worker_listen daemon pid:'.getmypid().' Stop');
	}
}

