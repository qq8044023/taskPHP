<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP 
 */
namespace taskphp;
/**
 * 工具类，主要放一些常用方法
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class Utils{
    protected static $instance;
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
        
            if ('m' == $dec) {
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
            $_mem[$start] = memory_get_usage();
        
        }
        return null;
    }
    /**
     * 获取配置参数
     * @param string    $name 配置参数名（支持二级配置 .号分割）
     * @return mixed
     */
    public static function config($name=''){
        return Config::get($name);
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
     * 缓存操作
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
     * @param string    $table 表名
     * @return mixed
     */
    public static function db($table=''){
        return new Db($table);
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
            return isset($_global_var[$name])?$_global_var[$name]:null;
        }else{//设置
            return $_global_var[$name]=$value;
        }
    }
}