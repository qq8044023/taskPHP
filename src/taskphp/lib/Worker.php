<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
namespace taskphp;
/**
 * 任务类
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class Worker{
	protected $_name;
	protected $_crontab;
	protected $_task;
	protected $_worker_str;
	protected $_skip;
	public function __construct($name,$task){
		$this->_skip=true;
		$this->_name=$name;
		if (!is_string($task)){
		    $this->_task=$task;
		}else{
		    $this->_worker_str=serialize($this->_task);
		}
	}
	/**
	 * 当任务超时未执行时,是否放弃期间的未执
	 * 设置未 false 且后台服务挂了后重启时,可能导致期间未执行任务的批量执行
	 * @param bool $skip
	 * @return \taskphp\Worker
	 */
	public function set_skip($skip){
		$this->_skip= (bool)$skip;
		return $this;
	}
	/**
	 * 得到是否放弃期间未执行任务设置
	 * @return boolean
	 */
	public function get_skip(){
		return $this->_skip;
	}
	/**
	 * 任务名,全局唯一,存在将导致任务覆盖
	 * @return string
	 */
	public function get_name(){
		return $this->_name;
	}
	/**
	 * 返回执行对象
	 * @return Task
	 */
	public function get_worker(){
		if ($this->_task==null){
			$task=@unserialize($this->_worker_str);
			if (!is_object($task)) return null;
			$this->_task=$task;
		}
		return $this->_task;
	}
	/**
	 * 返回序列化后的执行对象
	 * @return string
	 */
	public function get_worker_string(){
		if ($this->_worker_str==null) $this->_worker_str=serialize($this->_task);
		return $this->_worker_str;
	}
	/**
	 * 设置运行时间对象
	 * @param Crontab $crontab
	 * @return \taskphp\Worker
	 */
	public function set_crontab(Crontab $crontab){
		$this->_crontab=$crontab;
		return $this;
	}
	/**
	 * 返回运行时间对象
	 * @return \taskphp\Crontab
	 */
	public function get_crontab(){
		if ($this->_crontab==null)$this->_crontab=new Crontab();
		return $this->_crontab;
	}
	/**
	 * 计算下一次执行的时间
	 * @return int
	 */
	public function get_next_run_time($now_time=null){
	    $crontab=$this->get_crontab();
	    return Crontab::get_next_run_time($now_time,$crontab);
	}
}