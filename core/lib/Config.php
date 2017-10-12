<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
namespace core\lib;
/**
 * 配置处理类
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class Config{
    // 配置参数
    private static $_config = array(
        'core'=>array(
            'core_user'=>'nobody',//指定用户  nobody  www
            'memory_limit'=>'256M',//指定任务进程最大内存
            'worker_limit'=>0,//单个进程执行的任务数 0无限  大于0为指定数
            'worker_mode'=>0,//worker进程运行模式
        ),
    );
    // 参数作用域
    private static $_range = 'core';

    // 设定配置参数的作用域
    public static function range($range){
        self::$_range = $range;
        if (!isset(self::$_config[$range])) {
            self::$_config[$range] = array();
        }
    }

    /**
     * 加载配置
     */
    private static function load(){
        //加载系统配置
        $config=array();
        $file=TASKS_PATH.DS.'config'.EXT;
        if(is_file($file)){
            $config=include $file;
        }
        self::$_config['core']=array_merge(self::$_config['core'],$config);
        //加载用户任务配置
        $files=glob(TASKS_PATH.DS.'*'.DS.'config'.EXT);
        foreach($files as $file){
            $regex='/.*?tasks(.*?)config\.php.*?/';
            preg_match_all($regex, $file, $matches);
            $name=trim($matches[1][0],DS);
            self::$_config[$name]=include $file;
        }
    }

    /**
     * 检测配置是否存在
     * @param string    $name 配置参数名（支持二级配置 .号分割）
     * @param string    $range  作用域
     * @return bool
     */
    public static function has($name, $range = ''){
        self::load();
        $range = $range ?: self::$_range;
        if (!strpos($name, '.')) {
            return isset(self::$_config[$range][strtolower($name)]);
        } else {
            // 二维数组设置和获取支持
            $name = explode('.', $name, 2);
            return isset(self::$_config[$range][strtolower($name[0])][$name[1]]);
        }
    }

    /**
     * 获取配置参数 为空则获取所有配置
     * @param string    $name 配置参数名（支持二级配置 .号分割）
     * @param string    $range  作用域
     * @return mixed
     */
    public static function get($name = null, $range = ''){
        self::load();
        $range = $range ?: self::$_range;
        // 无参数时获取所有
        if (empty($name) && isset(self::$_config[$range])) {
            return self::$_config[$range];
        }

        if (!strpos($name, '.')) {
            return isset(self::$_config[$range][$name]) ? self::$_config[$range][$name] : null;
        } else {
            // 二维数组设置和获取支持
            $name    = explode('.', $name, 2);
            return isset(self::$_config[$range][$name[0]][$name[1]]) ? self::$_config[$range][$name[0]][$name[1]] : null;
        }
    }

    /**
     * 设置配置参数 name为数组则为批量设置
     * @param string|array  $name 配置参数名（支持二级配置 .号分割）
     * @param mixed         $value 配置值
     * @param string        $range  作用域
     * @return mixed
     */
    public static function set($name, $value = null, $range = ''){
        self::load();
        $range = $range ?: self::$_range;
        if (!isset(self::$_config[$range])) {
            self::$_config[$range] = array();
        }
        if (is_string($name)) {
            if (!strpos($name, '.')) {
                self::$_config[$range][strtolower($name)] = $value;
            } else {
                // 二维数组设置和获取支持
                $name = explode('.', $name, 2);
                self::$_config[$range][$name[0]][$name[1]] = $value;
            }
            return;
        } elseif (is_array($name)) {
            // 批量设置
            if (!empty($value)) {
                self::$_config[$range][$value] = isset(self::$_config[$range][$value]) ?
                array_merge(self::$_config[$range][$value], $name) :
                self::$_config[$range][$value] = $name;
                return self::$_config[$range][$value];
            } else {
                return self::$_config[$range] = array_merge(self::$_config[$range], array_change_key_case($name));
            }
        } else {
            // 为空直接返回 已有配置
            return self::$_config[$range];
        }
    }

    /**
     * 重置配置参数
     */
    public static function reset($range = ''){
        $range = $range ?: self::$_range;
        if (true === $range) {
            self::$_config = array();
        } else {
            self::$_config[$range] = array();
        }
    }
}
