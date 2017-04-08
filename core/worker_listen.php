<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
use core\lib\WorkerExe;
if(!defined('IS_CLI')){
    include_once __DIR__."/guide.php";
}
if(IS_CLI==false)die("plase run in cli".PHP_EOL);
$Daemon= new core\lib\Daemon();
$Daemon->worker_son();
$workerExe=WorkerExe::instance();
$workerExe->listen();