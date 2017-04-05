<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/TimePhp
 */
namespace core\lib;
/**
 * 工具类，主要放一些常用方法
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class Utils{
	/**
	 * 把timer对象转换成类似crontab时间字符串
	 * @param string $crontab
	 */
	public static function timer_to_string(Timer $timer){
		return implode(" ", array(
			self::_val_to_str($timer->get_seconds()),self::_val_to_str($timer->get_minutes()),self::_val_to_str($timer->get_month()),
			self::_val_to_str($timer->get_day()),self::_val_to_str($timer->get_month()),self::_val_to_str($timer->get_year()),
			self::_val_to_str($timer->get_week())
		));
	}
	
	/**
	 * 类似crontab的时间转成一个timer对象
	 * @param string $crontab
	 */
	public static function string_to_timer($crontab){
		$data=explode(" ", $crontab);
		foreach ($data as $k=>$v){
			$v=trim($v);
			if(empty($v)) unset($data[$k]);
		}
		if (count($data)!=7){
		    var_dump($data);
		    throw new Exception("timer string is wrong");
		}
		$timer= new Timer();
		$timer->set_seconds(self::_str_to_val($data[0]));
		$timer->set_minutes(self::_str_to_val($data[1]));
		$timer->set_hours(self::_str_to_val($data[2]));
		$timer->set_day(self::_str_to_val($data[3]));
		$timer->set_month(self::_str_to_val($data[4]));
		$timer->set_year(self::_str_to_val($data[5]));
		$timer->set_week(self::_str_to_val($data[6]));
		return $timer;
	}
	protected static function _val_to_str($val){
		if ($val===Timer::LOOP) return "*";
		return $val;
	}
	protected static function _str_to_val($val){
		if ($val=='*') return Timer::LOOP;
		if (Timer::is_loop($val)) return $val;
		return abs($val);
	}
	/**
	 * 生成任务名
	 * @return string
	 */
	public static function create_worker_name(){
		return uniqid('worker_');
	}
	/**
	 * 后台进程是否在运行
	 * @param array $process_name
	 * @return boolean
	 */
	public static function is_daemon($process_name=array()){
		$is_windows=DS=='\\';
		if (count($process_name)==0){
			if (!$is_windows){
			    $process_name=array("main.php");
			} else{
			    $process_name=array('distribute_listen.php','worker_listen.php');
			} 
		}
		ob_start();
		if (!$is_windows){
		    system('ps aux');
		} 
		else{
		    system('wmic  process where caption="php.exe" get caption,commandline /value');
		} 
		$ps=ob_get_contents();
		ob_end_clean();
		$ps = explode("\n", $ps);
		$out=[];
		foreach ($ps as $v){
			$v=trim($v);
			if (empty($v)){
			    continue;
			}
			$p=strrpos($v," ");
			if ($p===false){
			    continue;
			} 
			$out[]=trim(substr($v,$p));
		}
		foreach ($out as &$item){
		    if(strpos($item, DS)){
		        $item_arr=explode(DS, $item);
		        $item=end($item_arr);
		    }
		    $process_name=array_merge(array_diff($process_name, array($item)));
		}
		if(count($process_name)){
		    return false;
		}
		
		return true;
	}
}