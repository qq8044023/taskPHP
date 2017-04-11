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
		$re=Queue::lPush(static::$_worker_exec,$worker->get_worker());//加入队列
		if(!$re){
		    throw new Exception('function lPush error');
		}
	}
	
	/**
	 * 任务进行监听
	 */
	public function listen(){
		$run=true;
		if (defined('RUNER_FORK') && RUNER_FORK){
			if (!defined('RUNER_LIMIT')){
			    $run=1;
			}else{
				if(RUNER_LIMIT===true){
				    $run=true;
				} else{
				    $run=RUNER_LIMIT>0?RUNER_LIMIT:1;
				} 
			}
		}
		register_shutdown_function(array($this,'shutdown_function'));
		$taskManage=new TaskManage();
		while ($run===true||$run-->0){
			$this->_worker=Queue::brPop(static::$_worker_exec,0);//取出队列
			$taskManage->run_task($this->_worker);
		}
	}
	public function shutdown_function(){
		if(ob_get_level()<=0) return ;
		$data=ob_get_contents();
		ob_end_clean();
		if (empty($this->_worker))return ;
		Log::input(array($this->_worker,$data),1);
	}
}

