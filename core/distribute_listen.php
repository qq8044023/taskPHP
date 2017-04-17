<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
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