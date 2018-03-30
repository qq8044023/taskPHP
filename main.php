<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
//用户目录
define("TASKS_PATH", __DIR__.'/tasks');
//定义入口标记
define("START_PATH", __DIR__);

// 载入taskphp入口文件
require_once __DIR__.'/taskphp/base.php';

//自动加载为什么要写成函数？  这里是为了解决在多线程下自动加载不成功，需要内部重新初始化自动加载部分。
function load_locator(){
    //添加用户任务的命名空间前缀
    taskphp\Locator::getInstance()->addNamespace("tasks", TASKS_PATH.DS);
}
load_locator();

//系统配置
$config= include TASKS_PATH.'/config.php';
//加载配置信息
taskphp\Config::load($config);
//运行框架
taskphp\App::run();