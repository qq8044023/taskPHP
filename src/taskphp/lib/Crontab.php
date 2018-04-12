<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
namespace taskphp;
/**
 * 定时命令类
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class Crontab implements \Serializable{
	const LOOP=TRUE;
	protected $_hours;
	protected $_minutes;
	protected $_seconds;
	protected $_day;
	protected $_month;
	protected $_year;
	protected $_week;
	protected $_many_key='';
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
	 * 获取val列表内的当前值   用于单值多选设置
	 * @param int|array $val
	 * @param int $nval
	 * @return unknown
	 */
	protected function _get_now_val($key,$val,$nval){
	    if(is_array($val)){
	        sort($val,SORT_NUMERIC);
	        foreach ($val as $v){
	            if($v==$nval)  return $nval;
	        }
	        $this->_many_key=$key;
	        return $val[0];
	    }
	    return $nval;
	}
	/**
	 * 计算下一次执行的时间
	 * @return int
	 */
	public static function get_next_run_time($now_time=null,$crontab=null){
	    if ($now_time==null)$now_time=time();
	    $h=$crontab->get_hours();
	    $i=$crontab->get_minutes();
	    $s=$crontab->get_seconds();
	    $d=$crontab->get_day();
	    $m=$crontab->get_month();
	    $y=$crontab->get_year();
	    $w=$crontab->get_week();
	
	    $nh=date("G",$now_time);
	    $ni=intval(date("i",$now_time));
	    $ns=intval(date("s",$now_time));
	    $ny=date("Y",$now_time);
	    $nm=date("n",$now_time);
	    $nd=date("j",$now_time);
	    $nw=date("w",$now_time);
	    
	    //定量循环
	    if(self::is_loop($y)){
	        $loop_val=self::parse_loop($y);
	        $_m=self::is_fix($m)?$m:'01';
	        $_d=self::is_fix($d)?$d:'01';
	        $_h=self::is_fix($h)?$h:'00';
	        $_i=self::is_fix($i)?$i:'00';
	        $_s=self::is_fix($s)?$s:'00';
	        
	        /** 单值多选设置 开始 **/
	        $_m=$crontab->_get_now_val('months',$m,$nm);
	        $_d=$crontab->_get_now_val('day',$d,$nd);
	        $_h=$crontab->_get_now_val('hours',$h,$nh);
	        $_i=$crontab->_get_now_val('minute',$i,$ni);
	        $_now_time=strtotime("{$ny}-{$_m}-{$_d} {$_h}:{$_i}:{$_s}");
	        if($_now_time<$now_time){
	            if($crontab->_many_key=='months'){
	                $_now_time=strtotime("+ 1 year",$_now_time);
	            }elseif($crontab->_many_key=='day'){
	                $_now_time=strtotime("+ 1 months",$_now_time);
	            }elseif($crontab->_many_key=='hours'){
	                $_now_time=strtotime("+ 1 day",$_now_time);
	            }elseif($crontab->_many_key=='minute'){
	                $_now_time=strtotime("+ 1 hours",$_now_time);
	            }
	        }
	        
	        /** 单值多选设置 结束 **/
	        //单值多选设置  $now_time改为$_now_time
	        return strtotime(date("Y",strtotime("+ {$loop_val} year",$_now_time))."-{$_m}-{$_d} {$_h}:{$_i}:{$_s}");
	    }
	    if(self::is_loop($m)){
	        $loop_val=self::parse_loop($m);
	        $_d=self::is_fix($d)?$d:'01';
	        $_h=self::is_fix($h)?$h:'00';
	        $_i=self::is_fix($i)?$i:'00';
	        $_s=self::is_fix($s)?$s:'00';
	        /** 单值多选设置 开始 **/
	        $_m=$crontab->_get_now_val('months',$m,$nm);
	        $_d=$crontab->_get_now_val('day',$d,$nd);
	        $_h=$crontab->_get_now_val('hours',$h,$nh);
	        $_i=$crontab->_get_now_val('minute',$i,$ni);
	        $_now_time=strtotime("{$ny}-{$_m}-{$_d} {$_h}:{$_i}:{$_s}");
	        if($_now_time<$now_time){
	            if($crontab->_many_key=='months'){
	                $_now_time=strtotime("+ 1 year",$_now_time);
	            }elseif($crontab->_many_key=='day'){
	                $_now_time=strtotime("+ 1 months",$_now_time);
	            }elseif($crontab->_many_key=='hours'){
	                $_now_time=strtotime("+ 1 day",$_now_time);
	            }elseif($crontab->_many_key=='minute'){
	                $_now_time=strtotime("+ 1 hours",$_now_time);
	            }
	        }
	        /** 单值多选设置 结束 **/
	        //单值多选设置  $now_time改为$_now_time
	        return strtotime(date("Y-m",strtotime("+ {$loop_val} months",$_now_time))."-{$_d} {$_h}:{$_i}:{$_s}");
	    }
	    if(Crontab::is_loop($d)){
	        $loop_val=self::parse_loop($d);
	        $_h=self::is_fix($h)?$h:'00';
	        $_i=self::is_fix($i)?$i:'00';
	        $_s=self::is_fix($s)?$s:'00';
	        /** 单值多选设置 开始 **/
	        $_m=$crontab->_get_now_val('months',$m,$nm);
	        $_d=$crontab->_get_now_val('day',$d,$nd);
	        $_h=$crontab->_get_now_val('hours',$h,$nh);
	        $_i=$crontab->_get_now_val('minute',$i,$ni);
	        $_now_time=strtotime("{$ny}-{$_m}-{$_d} {$_h}:{$_i}:{$_s}");
	        if($_now_time<$now_time){
	            if($crontab->_many_key=='months'){
	                $_now_time=strtotime("+ 1 year",$_now_time);
	            }elseif($crontab->_many_key=='day'){
	                $_now_time=strtotime("+ 1 months",$_now_time);
	            }elseif($crontab->_many_key=='hours'){
	                $_now_time=strtotime("+ 1 day",$_now_time);
	            }elseif($crontab->_many_key=='minute'){
	                $_now_time=strtotime("+ 1 hours",$_now_time);
	            }
	        }
	        /** 单值多选设置 结束 **/
	        //单值多选设置  $now_time改为$_now_time
	        return strtotime(date("Y-m-d",strtotime("+ {$loop_val} day",$_now_time))." {$_h}:{$_i}:{$_s}");
	    }
	    if(self::is_loop($h)){
	        $loop_val=self::parse_loop($h);
	        $_i=self::is_fix($i)?$i:'00';
	        $_s=self::is_fix($s)?$s:'00';
	        /** 单值多选设置 开始 **/
	        $_m=$crontab->_get_now_val('months',$m,$nm);
	        $_d=$crontab->_get_now_val('day',$d,$nd);
	        $_h=$crontab->_get_now_val('hours',$h,$nh);
	        $_i=$crontab->_get_now_val('minute',$i,$ni);
	        $_now_time=strtotime("{$ny}-{$_m}-{$_d} {$_h}:{$_i}:{$_s}");
	        if($_now_time<$now_time){
	            if($crontab->_many_key=='months'){
	                $_now_time=strtotime("+ 1 year",$_now_time);
	            }elseif($crontab->_many_key=='day'){
	                $_now_time=strtotime("+ 1 months",$_now_time);
	            }elseif($crontab->_many_key=='hours'){
	                $_now_time=strtotime("+ 1 day",$_now_time);
	            }elseif($crontab->_many_key=='minute'){
	                $_now_time=strtotime("+ 1 hours",$_now_time);
	            }
	        }
	        /** 单值多选设置 结束 **/
	        //单值多选设置  $now_time改为$_now_time
	        return strtotime(date("Y-m-d H",strtotime("+ {$loop_val} hours",$_now_time)).":{$_i}:{$_s}");
	    }
	    if(Crontab::is_loop($i)){
	        $loop_val=self::parse_loop($i);
	        $_s=self::is_fix($s)?$s:'00';
	        /** 单值多选设置 开始 **/
	        $_m=$crontab->_get_now_val('months',$m,$nm);
	        $_d=$crontab->_get_now_val('day',$d,$nd);
	        $_h=$crontab->_get_now_val('hours',$h,$nh);
	        $_i=$crontab->_get_now_val('minute',$i,$ni);
	        $_now_time=strtotime("{$ny}-{$_m}-{$_d} {$_h}:{$_i}:{$_s}");
	        if($_now_time<$now_time){
	            if($crontab->_many_key=='months'){
	                $_now_time=strtotime("+ 1 year",$_now_time);
	            }elseif($crontab->_many_key=='day'){
	                $_now_time=strtotime("+ 1 months",$_now_time);
	            }elseif($crontab->_many_key=='hours'){
	                $_now_time=strtotime("+ 1 day",$_now_time);
	            }elseif($crontab->_many_key=='minute'){
	                $_now_time=strtotime("+ 1 hours",$_now_time);
	            }
	        }
	        /** 单值多选设置 结束 **/
	        //单值多选设置  $now_time改为$_now_time
	        return strtotime(date("Y-m-d H:i",strtotime("+ {$loop_val} minute",$_now_time)).":{$_s}");
	    }
	    if(self::is_loop($s)){
	        $loop_val=self::parse_loop($s);
	        /** 单值多选设置 开始 **/
	        $_m=$crontab->_get_now_val('months',$m,$nm);
	        $_d=$crontab->_get_now_val('day',$d,$nd);
	        $_h=$crontab->_get_now_val('hours',$h,$nh);
	        $_i=$crontab->_get_now_val('minute',$i,$ni);
	        $_now_time=strtotime("{$ny}-{$_m}-{$_d} {$_h}:{$_i}:{$ns}");
	        if($_now_time<$now_time){
	            if($crontab->_many_key=='months'){
	                $_now_time=strtotime("+ 1 year",$_now_time);
	            }elseif($crontab->_many_key=='day'){
	                $_now_time=strtotime("+ 1 months",$_now_time);
	            }elseif($crontab->_many_key=='hours'){
	                $_now_time=strtotime("+ 1 day",$_now_time);
	            }elseif($crontab->_many_key=='minute'){
	                $_now_time=strtotime("+ 1 hours",$_now_time);
	            }
	        }
	        /** 单值多选设置 结束 **/
	        //单值多选设置  $now_time改为$_now_time
	        return strtotime("+ {$loop_val} seconds",$_now_time);
	    }
	
	    //指定日期及循环
	    //* * * * * *
	    //s i G j n Y
	    //2016 11 11 22 22 22
	    //8-31 9-31 9-30
	    list($n,$_s)=self::_op_find($s, $ns,0, 59,false);
	    list($n,$_i)=self::_op_find($i, $ni,0, 59,!$n);
	    list($n,$_h)=self::_op_find($h, $nh,0, 23,!$n);//
	
	    if ($w!==self::LOOP){//以星期循环
	        list($n,$next)=self::_loop_find($w, $nw, 0, 6,!$n);
	        if ($n==true){
	            $nt=strtotime("+ {$next} day",$now_time);
	            $_y=date("Y",strtotime("+ {$next} day",$now_time));
	            $__y=$this->_val_find($y, $_y);
	            if ($__y!=$_y&&$y!==self::LOOP){//跨过年
	                $_y=$__y;
	                if ($_y===false) return false;//没下一年
	                $_m=1;
	                $_sw=date("w",strtotime(date("{$_y}-01-01 0:0:1")));//第一日星期
	                $w=is_array($w)?$w:array($w);
	                $_w=min($w);//最小星期
	                if ($_sw>$_w){
	                    $_d=6-$_sw+$w+2;//6-4+2 =4//4
	                }else $_d=$_w-$_sw+1;
	            }else{
	                $_m=date("n",$nt);
	                $_d=date("d",$nt);
	            }
	        }
	    }else{//以月日循环
	        list($n,$_d)=self::_op_find($d, $nd,1, date("t",$now_time),!$n);
	        $year=0;
	        $maxm=max(is_array($m)?$m:array($m));
	        $x=($n==true||$nm>$maxm)?false:true;
	        while (true){
	            //$m 规则 $nm 当前月份 8-31 9
	            list($_n,$_m)=self::_op_find($m, $nm,1,12,$x);
	            if ($_n)$year++;
	            if ($_d>self::_method_day($_m,date("L",strtotime(($ny+$year)."-{$_m}-01 0:00:01")))){
	                $x=false;
	                $nm=$_m;
	            }else break;
	        }
	        	
	        $_y=$ny+$year;
	
	        if ($y!==self::LOOP){
	            $bad=true;
	            $y=is_array($y)?$y:array($y);
	            $maxy=max($y);
	            $a=($_m==2&&$_d==29)?4:1;
	            while (true){
	                if (in_array($_y,$y)){
	                    unset($bad);break;
	                }
	                $_y+=$a;
	                if ($_y>$maxy)break;
	            }
	        }
	        	
	        if (isset($bad)) return false;
	    }
	    $dt="{$_y}-{$_m}-{$_d} {$_h}:{$_i}:{$_s}";
	    return strtotime($dt);
	}
	/**
	 * 得到某个月的最大天数
	 * @param unknown $m
	 * @param string $y
	 */
	protected static function _method_day($m,$y=true){
	    $m31=array(1,3,5,7,8,10,12);
	    $m30=array(4,6,9,11);
	    if(in_array($m, $m31)) return 31;
	    if(in_array($m, $m30)) return 30;
	    if ($y) return 29;
	    else return 28;
	}
	/**
	 * 查找某值的下一个规则值
	 * @param unknown $var
	 * @param unknown $now_var
	 */
	protected static function _val_find($var,$now_var){
	    $bad=true;
	    $var=is_array($var)?$var:array($var);
	    $max_var=max($var);
	    while (true){
	        if (in_array($now_var,$var)){
	            unset($bad);break;
	        }
	        $now_var+=1;
	        if ($now_var>$max_var)break;
	    }
	    if (isset($bad)) return false;//没下一年
	    return $now_var;
	}
	/**
	 * 按规则返回下个数间的间隔
	 * @param unknown $var
	 * @param unknown $now_var
	 * @param unknown $start
	 * @param unknown $end
	 * @param string $is_cp
	 */
	protected static function _loop_find($var,$now_var,$start,$end,$is_cp=false){
	    if($var===self::LOOP){
	        if ($is_cp){
	            return array(true,0);
	        }else{
	            $_m=false;
	            if ($now_var+1>$end){
	                $_m=true;
	            }
	            return array($_m,1);
	        }
	    }
	    if (!is_array($var))$var=array($var);
	    //4 156
	    $op=false;
	    $min=false;
	    $next=false;
	    while (count($var)){
	        $now=array_shift($var);
	        if ($now_var>$now){
	            if($min===false) $min=$now;
	            continue;
	        }
	        if ($now==$now_var){
	            $op=true;continue;
	        }
	        if($now>$now_var){
	            $next=$now;break;
	        }
	    }
	    if ($is_cp&&$op)$next=$now_var;
	    if ($next!==false) return array(true,$next-$now_var);
	    else{
	        if ($min!==false) return array(true,$end-$now_var+($min-$start)+1);//5
	        else return array(true,$end-$start+1);//
	    }
	}
	/**
	 * 按规则返回下个数
	 * @param unknown $var
	 * @param unknown $now_var
	 * @param unknown $start
	 * @param unknown $end
	 * @param string $is_cp
	 * @return multitype:boolean Ambigous <unknown, mixed>
	 */
	protected static function _op_find($var,$now_var,$start,$end,$is_cp=false){
	    $_m=false;
	    if($var===self::LOOP){
	        if ($is_cp){
	            $ns=$now_var;
	        }else{
	            $ns=$now_var+1;
	            if ($ns>$end){
	                $_m=true;
	                $ns=$start;
	            }
	        }
	    }else{
	        $var=is_array($var)?$var:array($var);
	        $_s=false;
	        foreach ($var as $v){
	            if ($is_cp?($v>=$now_var):($v>$now_var)){
	                $ns=$v;
	                $_s=true;
	                break;
	            }
	        }
	        if (!$_s){
	            $ns=min($var);
	            $_m=true;
	        }
	    }
	    return array($_m,$ns);
	}
	/**
	 * 把crontab对象转换成类似crontab时间字符串
	 * @param string $crontab
	 */
	public static function crontab_to_string(Crontab $crontab){
	    return implode(" ", array(
	        self::_val_to_str($crontab->get_seconds()),self::_val_to_str($crontab->get_minutes()),self::_val_to_str($crontab->get_hours()),
	        self::_val_to_str($crontab->get_day()),self::_val_to_str($crontab->get_month()),self::_val_to_str($crontab->get_year()),
	        self::_val_to_str($crontab->get_week())
	    ));
	}
	
	/**
	 * 类似crontab的时间转成一个crontab对象
	 * @param string $crontab
	 */
	public static function string_to_crontab($crontab){
	    $data=explode(" ", $crontab);
	    foreach ($data as $k=>$v){
	        $v=trim($v);
	        if(empty($v)) unset($data[$k]);
	    }
	    if (count($data)!=7){
	        throw new Exception("crontab string is wrong");
	    }
	    $crontab= new Crontab();
	    $crontab->set_seconds(self::_str_to_val($data[0]));
	    $crontab->set_minutes(self::_str_to_val($data[1]));
	    $crontab->set_hours(self::_str_to_val($data[2]));
	    $crontab->set_day(self::_str_to_val($data[3]));
	    $crontab->set_month(self::_str_to_val($data[4]));
	    $crontab->set_year(self::_str_to_val($data[5]));
	    $crontab->set_week(self::_str_to_val($data[6]));
	    return $crontab;
	}
	protected static function _val_to_str($val){
	    if ($val===self::LOOP) return "*";
	    if(is_array($val)) return implode(',', $val);
	    return $val;
	}
	protected static function _str_to_val($val){
	    if ($val=='*') return self::LOOP;
	    /** 单值多选设置 开始 **/
	    if (false !== strpos($val, ',')) {
            $val = explode(',', $val);
            return $val;
        }
        /** 单值多选设置 结束 **/
	    if (self::is_loop($val)) return $val;
	    return abs($val);
	}
	
	/**
	 * 通过时间戳设置运行时间
	 * 必须大于当前时间
	 * @param int $time
	 * @return Crontab
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
	 * @return \taskphp\Crontab
	 */
	public function set_hours($hours){
		$this->_hours=$this->_limit_set($hours, 0, 23,true);
		return $this;
	}
	/**
	 * 设定运行 秒
	 * @param mixed $seconds
	 * @return \taskphp\Crontab
	 */
	public function set_seconds($seconds){
		$this->_seconds=$this->_limit_set($seconds, 0, 59,true);
		return $this;
	}
	/**
	 * 设定运行 分
	 * @param mixed $minutes
	 * @return \taskphp\Crontab
	 */
	public function set_minutes($minutes){
		$this->_minutes=$this->_limit_set($minutes, 0, 59,true);
		return $this;
	}
	/**
	 * 设定运行 天 如果只有2月不允许出现30 31
	 * @param mixed $day
	 * @return \taskphp\Crontab
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
	 * @return \taskphp\Crontab
	 */
	public function set_month($month){
		$this->_month=$this->_limit_set($month, 1, 12,true);
		$this->_check_day();
		return $this;
	}
	/**
	 * 设定运行 年 如果只有2月不允许出现30 31
	 * @param mixed $day
	 * @return \taskphp\Crontab
	 */
	public function set_year($year){
		$this->_year=$this->_limit_set($year, date('Y'), 9999,true);
		return $this;
	}
	/**
	 * 设定运行 星期 如果设置此值,将导致 日 月设置无效,任务按星期循环
	 * @param mixed $day
	 * @return \taskphp\Crontab
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