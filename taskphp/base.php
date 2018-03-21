<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
//版本号
define('TASKPHP_VERSION', '2.1');
//开始时间记录
define('TASKPHP_START_TIME', microtime(true));
//开始 内存量记录
define('TASKPHP_START_MEM', memory_get_usage());
//是否cli模式
define("IS_CLI", (PHP_SAPI=='cli') ? true : false);
//分割符
define("DS", DIRECTORY_SEPARATOR);
//系统内核跟目录
define("TASKPHP_PATH", dirname(__FILE__));
//php文件后缀
define("EXT", ".php");

// 载入Loader类
require_once TASKPHP_PATH.DS."Loader".EXT;
$locator = \taskphp\Locator::getInstance();
//添加框架目录
$locator->addNamespace("taskphp", TASKPHP_PATH.DS);

//注册异常捕捉
$Exception = new \taskphp\Exception();
$Exception->register();