<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
namespace taskphp;
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
    public function run() {
        if($this->_worker!=null){
            //重载框架的locator
            if(function_exists('load_locator')){
                call_user_func('load_locator');
            }
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