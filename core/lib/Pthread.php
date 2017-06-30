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
    public $_taskManage;
    public $_worker;
    public function __construct($taskManage,$worker) {
        $this->_taskManage=$taskManage;
        $this->_worker=$worker;
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
        if($this->_worker!=null){
            $this->reload_locator();
            $this->_taskManage->run_task($this->_worker);
        }
    }
    /**
     * 调用
     **/
    public static function call($taskManage,$worker){
        $thread = new Pthread($taskManage,$worker);
        if($thread->start()){
            $thread->join();
        }else{
            $thread->kill();
        }
    }
}