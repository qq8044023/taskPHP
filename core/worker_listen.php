<?php
/**
 * 任务执行后台进程
 * @author cqcqphper 小草<cqcqphper@163.com>
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