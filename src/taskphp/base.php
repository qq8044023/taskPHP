<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
//版本号
define('TASKPHP_VERSION', '3.0');
//设置中国的时区
date_default_timezone_set('Asia/Chongqing');
//开始时间记录
define('TASKPHP_START_TIME', microtime(true));
//开始 内存量记录
define('TASKPHP_START_MEM', memory_get_usage());
if(!defined('IS_CLI')){
    //是否cli模式
    define("IS_CLI", (PHP_SAPI=='cli') ? true : false);
}
if(!defined('DS')){
    //分割符
    define("DS", DIRECTORY_SEPARATOR);
}
//系统内核跟目录
define("TASKPHP_PATH", dirname(__FILE__));
// 载入Loader类
require_once TASKPHP_PATH.DS.'lib'.DS."Loader.php";
$locator = taskphp\Locator::getInstance();

//注册异常捕捉
$Exception = new taskphp\Exception();
$Exception->register();