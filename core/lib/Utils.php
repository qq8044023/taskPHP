<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
namespace core\lib;
/**
 * 工具类，主要放一些常用方法
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class Utils{
    
    public static function check_worker_fork(){
        return self::get_os() != 'win' && self::check_pcntl();
    }
    
    public static function is_worker_fork(){
        $worker_mode=Config::get('worker_mode');
        if($worker_mode===0){
            if(self::check_worker_fork())return true;
        }elseif($worker_mode===1){
            if(self::check_worker_fork())return true;
        }
        return false;
    }
    
    
    public static function get_os(){
        $os='unix';
        if(DS=='\\')$os='win';
        return $os;
    }
    
    public static function check_pthreads(){
        return extension_loaded('pthreads');
    }
    public static function is_pthreads(){
        $worker_mode=Config::get('worker_mode');
        if($worker_mode===0){
            if(self::check_pthreads())return true;
        }elseif($worker_mode===3){
            if(self::check_pthreads())return true;
        }
        return false;
    }
    
    public static function is_popen(){
        $worker_mode=Config::get('worker_mode');
        if($worker_mode===0){
            if(!self::check_pthreads())return true;
        }elseif($worker_mode===2){
            return true;
        }
        return false;
    }
    
    public static function check_pcntl(){
        return extension_loaded('pcntl');
    }
    /**
     * 特殊字符串替换
     * @param unknown $str
     * @return string|unknown  */
    static public function replace_keyword($str){
        $repArr=["@",".","!","&","$"];
        $repStr="";
        foreach (str_split($str) as $v){
            $repStr.=in_array($v, $repArr)?"\\".$v:$v;
        }
        return $repStr;
    }
    /**
     * 导入所需的类库
     *  * @param unknown $str
     */
    static public function import($path) {
        include_once APP_ROOT.DS.str_replace("@",DS,$path).EXT;
    }
    /**
     * 获取时间是星期几
     * @param unknown $date 2017-12-23
     * @param string $dateType  false 数字  true 中文
     * @return 星期几
     *   */
    static public function getWeek($date,$dateType=false){
        //强制转换日期格式
        $date_str=date('Y-m-d',strtotime($date));
    
        //封装成数组
        $arr=explode("-", $date_str);
         
        //参数赋值
        //年
        $year=$arr[0];
         
        //月，输出2位整型，不够2位右对齐
        $month=sprintf('%02d',$arr[1]);
         
        //日，输出2位整型，不够2位右对齐
        $day=sprintf('%02d',$arr[2]);
         
        //时分秒默认赋值为0；
        $hour = $minute = $second = 0;
         
        //转换成时间戳
        $strap = mktime($hour,$minute,$second,$month,$day,$year);
         
        //获取数字型星期几
        $number_wk=date("w",$strap);
        $weekArr=array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
        if ($dateType==false){
            $weekArr=array(0,1,2,3,4,5,6);
        }
        //获取数字对应的星期
        return $weekArr[$number_wk];
    }
    static public function writeLog($variables , $show = false , $logPath = APP_ROOT.DS."log.txt"){
        ob_start();
        if (is_array($variables) && true == $show)
        {
            print_r($variables);
        } else {
            var_dump($variables);
        }
        $data = ob_get_clean();
        @file_put_contents($logPath , $data);
    }
}