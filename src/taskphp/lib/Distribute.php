<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
namespace taskphp;
/**
 * 任务派发类
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class Distribute{
	/**
	 * @var TaskManage
	 */
	protected $_TaskManage;

	/**
	 * @var WorkerRun[]
	 */
	protected $_WorkerRuns;
	/**
	 * @var int[]
	 */
	protected $_times;
	
	public function set_task_manage(TaskManage $taskManage){
	    $this->_TaskManage=$taskManage;
	    return $this;
	}
	
	/**
	 * 初始化变量环境
	 * @return taskphp\Distribute
	 */
	public function init(){
		$workers=$this->_TaskManage->run_worker_list();
		$now_time=time();
		$this->_WorkerRuns=array();
		$this->_times=array();
		foreach ($workers as $key=>$value){
			$next_time=$value->get_run_time();
			$offtime=$next_time-$now_time;
			if ($offtime<=0){
				$this->_WorkerRuns[]=$value;
				if ($value->get_worker()->get_skip())$run_time=$now_time;
				else $run_time=$value->get_run_time();
				$next_time=$value->get_worker()->get_next_run_time($run_time);
				$this->_TaskManage->next_time_worker($value->get_worker(),$next_time);
				if ($next_time===false)continue;
				$offtime=$next_time-$now_time;
				if ($offtime<=0)$offtime=0;
			}else unset($value);
			$this->_times[]=$offtime;
		}
		unset($workers);
		return $this;
	}
	/**
	 * 执行任务
	 * @return taskphp\Distribute
	 */
	public function exec_worker(){
		foreach ($this->_WorkerRuns as $key=>$value){
			$this->_TaskManage->exec_worker($value->get_worker());
			unset($value,$this->_WorkerRuns[$key]);
		}
		return $this;
	}
	/**
	 * 执行暂停
	 * @return taskphp\Distribute
	 */
	public function sleep(){
		if (count($this->_times)==0) $sleep_time=0;
		else{
			$sleep_time=intval(min($this->_times));
			if ($sleep_time==0) return $this;
		}
		$this->_TaskManage->on_sleep($sleep_time);
		return $this;
	}
	/**
	 * 后台监听
	 */
	public function listen(){
	    Utils::log('distribute_listen daemon pid:'.getmypid().' Start');
	    register_shutdown_function(array($this,'shutdown_function'));
		while (true){
			$this->init()->exec_worker()->sleep();
		}
	}
	public function shutdown_function(){
	    Utils::log('distribute_listen daemon pid:'.getmypid().' Stop');
	    //后面研究下有没有办法重启这个进程
	}
}