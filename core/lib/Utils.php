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
     *  * @param string $str
     */
    static public function loadphp($path) {
        $filename=APP_ROOT.DS.str_replace(".",DS,$path).EXT;
        $res=in_array($filename,get_included_files());
        if(!$res) include_once $filename;
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
    /**
     * 根据PHP各种类型变量生成唯一标识号
     * @param mixed $mix 变量
     * @return string
     */
    public static function to_guid_string($mix){
        if (is_object($mix)) {
            return spl_object_hash($mix);
        } elseif (is_resource($mix)) {
            $mix = get_resource_type($mix) . strval($mix);
        } else {
            $mix = serialize($mix);
        }
        return md5($mix);
    }
    /**
     * 设置和获取统计数据
     * 使用方法:
     * <code>
     * Utils::counter('db',1); // 记录数据库操作次数
     * Utils::counter('read',1); // 记录读取次数
     * echo Utils::counter('db'); // 获取当前页面数据库的所有操作次数
     * echo Utils::counter('read'); // 获取当前页面读取次数
     * </code>
     * @param string $key 标识位置
     * @param integer $step 步进值
     * @param boolean $save 是否保存结果
     * @return mixed
     */
    public static function counter($key, $step = 0, $save = false){
        static $_num = array();
        if (!isset($_num[$key])) {
            $_num[$key] = (false !== $save) ? Queue::get('counter_' . $key) : 0;
        }
        if (empty($step)) {
            return $_num[$key];
        } else {
            $_num[$key] = $_num[$key] + (int) $step;
        }
        if (false !== $save) {
            // 保存结果
            Queue::set('counter_' . $key, $_num[$key], $save);
        }
        return null;
    }
    
    /**
     * 记录和统计时间（微秒）和内存使用情况
     * 使用方法:
     * <code>
     * Utils::statistics('begin'); // 记录开始标记位
     * // ... 区间运行代码
     * Utils::statistics('end'); // 记录结束标签位
     * echo Utils::statistics('begin','end',6); // 统计区间运行时间 精确到小数后6位
     * echo Utils::statistics('begin','end','m'); // 统计区间内存使用情况
     * 如果end标记位没有定义，则会自动以当前作为标记位
     * 其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
     * </code>
     * @param string $start 开始标签
     * @param string $end 结束标签
     * @param integer|string $dec 小数位或者m
     * @return mixed
     */
    public static function statistics($start, $end = '', $dec = 4){
        static $_info = array();
        static $_mem  = array();
        if (is_float($end)) {
            // 记录时间
            $_info[$start] = $end;
        } elseif (!empty($end)) {
            // 统计时间和内存使用
            if (!isset($_info[$end])) {
                $_info[$end] = microtime(true);
            }
        
            if (MEMORY_LIMIT_ON && 'm' == $dec) {
                if (!isset($_mem[$end])) {
                    $_mem[$end] = memory_get_usage();
                }
        
                return number_format(($_mem[$end] - $_mem[$start]) / 1024);
            } else {
                return number_format(($_info[$end] - $_info[$start]), $dec);
            }
        
        } else {
            // 记录时间和内存使用
            $_info[$start] = microtime(true);
            if (MEMORY_LIMIT_ON) {
                $_mem[$start] = memory_get_usage();
            }
        
        }
        return null;
    }
    /**
     * 获取配置参数
     * @param string    $name 配置参数名（支持二级配置 .号分割）
     * @param string    $range  作用域
     * @return mixed
     */
    public static function config($name,$range){
        return Config::get($name,$range);
    }
    /**
     * 写日志
     * @param unknown $data 欲写入的数据
     * @param int $type 日志等级 0:DEBUG正常 1:INFO正常  2:WARN警告 3:ERROR错误 4:FATAL致命错误   默认0
     */
    public static function log($data,$type=0){
        Log::input($data,$type);
    }
    /**
     * 缓存管理
     * @param mixed $name 缓存名称
     * @param mixed $value 缓存值
     * @return mixed
     */
    public static function cache($name, $value = ''){
        if ('' === $value) {
            // 获取缓存
            return Queue::get($name);
        } elseif (is_null($value)) {
            // 删除缓存
            return Queue::rm($name);
        } else {
            // 缓存数据
            return Queue::set($name, $value);
        }
    }
    /**
     * 获取数据库连接对象
     * @param string    $config 配置信息
     * @return mixed
     */
    public static function db($config){
        return Db::setConfig($config);
    }
    /**
     * 路径转义
     * @param unknown $path
     * @return string  */
    public static function escapePath($path){
        return addcslashes($path,"\t\r\n\d");
    }
    /**
     * 系统配置文件
     * @param unknown $name
     * @param unknown $value
     * @param unknown $default
     * @return array|string|mixed|NULL|string  */
    static public function dbConfig($name=null, $value=null,$default=null) {
        static $_config = array();
        // 无参数时获取所有
        if (empty($name)) {
            return $_config;
        }
        // 优先执行设置获取或赋值
        if (is_string($name)) {
            if (!strpos($name, '.')) {
                $name = strtoupper($name);
                if (is_null($value))
                    return isset($_config[$name]) ? $_config[$name] : $default;
                    $_config[$name] = $value;
                    return null;
            }
            // 二维数组设置和获取支持
            $name = explode('.', $name);
            $name[0]   =  strtoupper($name[0]);
            if (is_null($value))
                return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
                $_config[$name[0]][$name[1]] = $value;
                return null;
        }
        // 批量设置
        if (is_array($name)){
            $_config = array_merge($_config, array_change_key_case($name,CASE_UPPER));
            return null;
        }
        return null; // 避免非法参数
    }
    /**
     * 数据库实例化
     * @param string $name
     * @param string $tablePrefix
     * @param string $connection
     * @return unknown  */
    static public function model($name='', $tablePrefix='',$connection='') {
        static $_model  = array();
        if(strpos($name,':')) {
            list($class,$name)    =  explode(':',$name);
        }else{
            $class      =   'core\\lib\\Model';
        }
        $guid           =   (is_array($connection)?implode('',$connection):$connection).$tablePrefix . $name . '_' . $class;
        if (!isset($_model[$guid]))
            $_model[$guid] = new $class($name,$tablePrefix,$connection);
            return $_model[$guid];
    }
    /**
     * 字符串命名风格转换parse_name
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @param string $name 字符串
     * @param integer $type 转换类型
     * @return string
     */
    static public function parseName($name, $type=0) {
        if ($type) {
            return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function($match){return strtoupper($match[1]);}, $name));
        } else {
            return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
    }
    /**
     * 检查端口是否可以被绑定
     * @param unknown $host
     * @param unknown $port
     */
    static public function checkPortBindable($host, $port){
        $result=false;
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_nonblock($sock);
        socket_connect($sock,$host, $port);
        socket_set_block($sock);
        $r = array($sock);
        $w = array($sock);
        $f = array($sock);
        if(socket_select($r, $w,$f , 5)===1){
            $result=true;
        }
        if(self::get_os()!='win'){
            $result=true;
        }
        return $result;
    }
    
    /**
     * 单进程 全局变量的存放和获取
     * @param string $name
     * @param string $value
     */
    static public function global_var($name=null, $value=null){
        static $_global_var  = array();
        if($name===null){
            return $_global_var;
        }
        if($value===null){//获取
            return $_global_var[$name];
        }else{//设置
            return $_global_var[$name]=$value;
        }
    }
}