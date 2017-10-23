<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
date_default_timezone_set('Asia/Chongqing');
//版本号
define('TASKPHP_VERSION', '2.0');
//开始时间记录
define('TASKPHP_START_TIME', microtime(true));
//开始 内存量记录
define('TASKPHP_START_MEM', memory_get_usage());
//是否cli模式
define("IS_CLI", (PHP_SAPI=='cli') ? true : false);
//分割符
define("DS", DIRECTORY_SEPARATOR);
//项目跟目录
define("APP_ROOT", substr(dirname(__FILE__),0,-5));
//任务跟目录
define("TASKS_PATH", APP_ROOT.DS."tasks");
//系统内核跟目录
define("CORE_PATH", APP_ROOT.DS."core");
//日志跟目录
define("LOGS_PATH", APP_ROOT.DS."logs");
//php文件后缀
define("EXT", ".php");

// 载入Loader类
require_once CORE_PATH.DS."lib".DS."Loader".EXT;
$locator = \core\lib\Locator::getInstance();
//添加框架目录
$locator->addNamespace("core", CORE_PATH.DS);
//添加框架用户任务目录
$locator->addNamespace("tasks", TASKS_PATH.DS);

//注册异常捕捉
$Exception = new \core\lib\Exception();
$Exception->register();