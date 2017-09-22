<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
namespace core\lib;
/**
 * 多线程操作类
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class Pthread extends \Thread{
    public $_task;
    public function __construct($task) {
        $this->_task=$task;
    }
    /**
     * 重载框架的locator
     */
    protected function reload_locator(){
        date_default_timezone_set( 'Asia/Chongqing');
        $locator = \core\lib\Locator::getInstance();
        //添加框架目录
        $locator->addNamespace("core", CORE_PATH.DS);
        //添加框架用户任务目录
        $locator->addNamespace("tasks", TASKS_PATH.DS);
        //注册
        $locator->register();
    }
    public function run() {
        if($this->_task!=null){
            $this->reload_locator();
            TaskManage::run_task($this->_task);
        }
    }
    /**
     * 调用
     **/
    public static function call($task){
        $thread = new Pthread($task);
        if($thread->start()){
            $thread->join();
        }else{
            $thread->kill();
        }
    }
}