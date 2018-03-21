<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */

date_default_timezone_set('Asia/Chongqing');
//系统内核跟目录
define("TASKS_PATH", dirname(__FILE__));
// 载入taskphp入口文件
require_once dirname(TASKS_PATH).'/taskphp/base.php';
//添加用户目录
$locator->addNamespace("tasks", TASKS_PATH.DS);
