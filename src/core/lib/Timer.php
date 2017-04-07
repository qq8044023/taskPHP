<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
namespace core\lib;
/**
 * 定时器类
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class Timer implements \Serializable{
	const LOOP=TRUE;
	protected $_hours;
	protected $_minutes;
	protected $_seconds;
	protected $_day;
	protected $_month;
	protected $_year;
	protected $_week;
	/**
	 * 循环标记
	 * @var string
	 */
	private static $_P='/';
	
	/**
	 * 任务时间,不做任何设置,按每分钟执行一次
	 */
	public function __construct(){
		$this->_hours=self::LOOP;
		$this->_minutes=self::LOOP;
		$this->_seconds=0;
		$this->_day=self::LOOP;
		$this->_month=self::LOOP;
		$this->_year=self::LOOP;
		$this->_week=self::LOOP;
	}
	
	/**
	 * 创建定时循环
	 * 设置循环值,比循环值小的单位必须为固定值
	 * 比循环值大的单位作废,如:设置日循环,月跟年的设置作废
	 * @param int $var
	 * @return string
	 */
	public static function create_loop($var){
		return self::$_P.intval($var);
	}
	/**
	 * 解析一个定时循环,返回具体值
	 * @param string $var
	 * @return boolean|number
	 */
	public static function parse_loop($var){
		if (!self::is_loop($var)) return false;
		return intval(substr($var, 1));
	}
	/**
	 * 指定值是否是定时循环值
	 * @param string $var
	 * @return boolean
	 */
	public static function is_loop($var){
		if ($var===self::LOOP) return false;
		if (is_array($var)) return false;
		return self::$_P==substr($var,0,1);
	}
	/**
	 * 指定值是否是单一固定值
	 * @param mixed $var
	 * @return boolean
	 */
	public static function is_fix($var){
		if ($var===self::LOOP) return false;
		return !is_array($var);
	}
	/**
	 * 通过时间戳设置运行时间
	 * 必须大于当前时间
	 * @param int $time
	 * @return Timer
	 */
	public function set_time($time){
		$time=$time<time()?time():$time;
		$this->set_week(self::LOOP);
		$this->set_year(date("Y",$time))
			->set_month(date("n",$time))
			->set_day(date("j",$time))
			->set_hours(date("G",$time))
			->set_minutes(intval(date("i",$time)))
			->set_seconds(intval(date("s",$time)));
		return $this;
	}
	/**
	 * 过滤设置变量
	 * @param mixed $vars
	 * @param int $start
	 * @param int $end
	 * @param bool $allow_loop 
	 * @throws Exception
	 * @return mixed
	 */
	protected function _limit_set($vars,$start,$end,$allow_loop=false){
		if ($vars===self::LOOP)return $vars;
		else{
			if (is_array($vars)){
				foreach ($vars as &$v){
					$v=intval($v);
					$v=$v>$end?$start:$v;
					$v=$v<$start?$start:$v;
				}
				if (count($vars)==0) throw new Exception("not support set empty data");
				if (count($vars)>1){
					sort($vars,SORT_NUMERIC);
					return array_unique($vars);
				}else $vars=array_pop($vars);
			}
			if($allow_loop&&self::is_loop($vars)){
				$vars=str_replace(self::$_P, '', $vars);
				$vars=intval($vars);
				return self::$_P.$vars;
			}
			$vars=intval($vars);
			$vars=$vars>$end?$start:$vars;
			$vars=$vars<$start?$start:$vars;
			return $vars;
		}
	}
	/**
	 * 设定运行 时
	 * @param mixed $hours
	 * @return \core\lib\Timer
	 */
	public function set_hours($hours){
		$this->_hours=$this->_limit_set($hours, 0, 23,true);
		return $this;
	}
	/**
	 * 设定运行 秒
	 * @param mixed $seconds
	 * @return \core\lib\Timer
	 */
	public function set_seconds($seconds){
		$this->_seconds=$this->_limit_set($seconds, 0, 59,true);
		return $this;
	}
	/**
	 * 设定运行 分
	 * @param mixed $minutes
	 * @return \core\lib\Timer
	 */
	public function set_minutes($minutes){
		$this->_minutes=$this->_limit_set($minutes, 0, 59,true);
		return $this;
	}
	/**
	 * 设定运行 天 如果只有2月不允许出现30 31
	 * @param mixed $day
	 * @return \core\lib\Timer
	 */
	public function set_day($day){
		$this->_day=$this->_limit_set($day, 1, 31,true);
		$this->_check_day();
		return $this;
	}
	protected function _check_day(){
		if (!is_array($this->_month)&&intval($this->_month)===2){
			$day=is_array($this->_day)?$this->_day:array($this->_day);
			//当只有2月时不能设置 30或31
			if (in_array(30, $day)||in_array(31, $day)) throw new Exception("2 month not support day 30 or 31");
		}
	}
	/**
	 * 设定运行 月 如果只有2月不允许出现30 31
	 * @param mixed $day
	 * @return \core\lib\Timer
	 */
	public function set_month($month){
		$this->_month=$this->_limit_set($month, 1, 12,true);
		$this->_check_day();
		return $this;
	}
	/**
	 * 设定运行 年 如果只有2月不允许出现30 31
	 * @param mixed $day
	 * @return \core\lib\Timer
	 */
	public function set_year($year){
		$this->_year=$this->_limit_set($year, date('Y'), 9999,true);
		return $this;
	}
	/**
	 * 设定运行 星期 如果设置此值,将导致 日 月设置无效,任务按星期循环
	 * @param mixed $day
	 * @return \core\lib\Timer
	 */
	public function set_week($week){
		$this->_week=$this->_limit_set($week, 0, 6);
		return $this;
	}
	/**
	 * 取得 时
	 * @return mixed
	 */
	public function get_hours(){
		return $this->_hours;
	}
	/**
	 * 取得 秒
	 * @return mixed
	 */
	public function get_seconds(){
		return $this->_seconds;
	}
	/**
	 * 取得 分
	 * @return mixed
	 */
	public function get_minutes(){
		return $this->_minutes;
	}
	/**
	 * 取得 日
	 * @return mixed
	 */
	public function get_day(){
		return $this->_day;
	}
	/**
	 * 取得 月
	 * @return mixed
	 */
	public function get_month(){
		return $this->_month;
	}
	/**
	 * 取得 年
	 * @return mixed
	 */
	public function get_year(){
		return $this->_year;
	}
	/**
	 * 取得 星期
	 * @return mixed
	 */
	public function get_week(){
		return $this->_week;
	}
	/**
	 * {@inheritDoc}
	 * @see Serializable::serialize()
	 */
	public function serialize(){
		return serialize(array($this->_year,$this->_month,$this->_day,$this->_hours,$this->_minutes,$this->_seconds,$this->_week));
	}
	/**
	 * {@inheritDoc}
	 * @see Serializable::unserialize()
	 */
	public function unserialize($s){
		list($this->_year,$this->_month,$this->_day,$this->_hours,$this->_minutes,$this->_seconds,$this->_week)=unserialize($s);
	}
}