<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
namespace taskphp;
/**
 * 执行任务中间类
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class WorkerRun{
	protected $_worker;
	protected $_run_time;
	/**
	 * 执行任务中间对象
	 * @param Worker $worker
	 * @param int $run_time
	 */
	public function __construct(Worker $worker,$run_time){
		$this->_worker=$worker;
		$this->_run_time=$run_time;
	}
	/**
	 * 返回需要执行任务
	 * @return Worker
	 */
	public function get_worker(){
		return $this->_worker;
	}
	/**
	 * 返回该任务执行时间
	 */
	public function get_run_time(){
		return $this->_run_time;
	}
	public function __destruct(){
		unset($this->_worker);
		unset($this->_run_time);
	}
}