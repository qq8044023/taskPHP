<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
namespace core\lib;
use core\lib\Exception;
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
	public static $_worker_exec="task_exec";

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
		Queue::lPush(static::$_worker_exec,$worker->get_worker());//加入队列
	}
	
	/**
	 * 任务进行监听
	 */
	public function listen(){
		if (defined('WORKER_FORK') && WORKER_FORK){//多进程模式
			$run=Config::get('worker_limit');
			$run=($run==0)?true:$run;
		}else{//单进程模式
		    $run=true;
		}
		$taskManage=new TaskManage();
		register_shutdown_function(array($this,'shutdown_function'));
		while ($run===true||$run-->0){
			$this->_worker=Queue::brPop(static::$_worker_exec,0);//取出队列
			if(Utils::is_pthreads()){//多线程模式
			    Pthread::call($taskManage,$this->_worker);
			}elseif(Utils::is_popen()){//单线程模式
			    $taskManage->run_task($this->_worker);
			}
		}
	}
	public function shutdown_function(){
		if(ob_get_level()<=0) return ;
		$data=ob_get_contents();
		ob_end_clean();
		if (empty($this->_worker))return ;
		Utils::log('pid:'.getmypid().' Stop');
	}
}

