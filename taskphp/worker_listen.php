<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
use taskphp\Command;
use taskphp\WorkerExe;
if(!defined('IS_CLI')){
    include_once __DIR__."/guide.php";
}
if(IS_CLI==false)die("plase run in cli".PHP_EOL);
Command::analysis();
$task_name=Command::$_cmd_key;
$workerExe=WorkerExe::instance();
$workerExe->listen($task_name);