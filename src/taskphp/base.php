<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
version_compare(PHP_VERSION,'5.5.0','<') && die('PHP version is too low, at least PHP5.5 is needed. Please upgrade PHP version.');
//版本号
define('TASKPHP_VERSION', '3.0');
//设置中国的时区
date_default_timezone_set('Asia/Chongqing');
//开始时间记录
define('TASKPHP_START_TIME', microtime(true));
//开始 内存量记录
define('TASKPHP_START_MEM', memory_get_usage());
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