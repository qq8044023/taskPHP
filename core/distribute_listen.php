<?php
/**
 * 任务派发后台进程
 * @author cqcqphper 小草<cqcqphper@163.com>
 */
use core\lib\Distribute;
use core\lib\TaskManage;
if(!defined('IS_CLI')){
    include_once __DIR__."/guide.php";
}
if(IS_CLI==false)die("plase run in cli".PHP_EOL);
$taskManage=new TaskManage();
$taskManage->load_worker();
$distribute=new Distribute();
$distribute->set_task_manage($taskManage);
$distribute->listen();