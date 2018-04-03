<?php
//定义启动文件入口标记
define("START_PATH", __DIR__);
// 载入taskphp入口文件
require_once dirname(__DIR__).'/src/taskphp/base.php';
//自动加载为什么要写成函数？  这里是为了解决在多线程下自动加载不成功，需要内部重新初始化自动加载部分。
function load_locator(){
    //添加用户任务的命名空间前缀
    taskphp\Locator::getInstance()->addNamespace("examples", START_PATH.DS);
}
load_locator();
//系统配置
$config= include START_PATH.'/config.php';
//加载配置信息
taskphp\Config::load($config);
//运行框架
taskphp\App::run();