<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
namespace core\lib;
use core\lib\Utils;
/**
 * 任务接口
 * @author cqcqphper 小草<cqcqphper@163.com>
 */
interface SerializableBase extends \Serializable{}
class Task implements SerializableBase{
    /**
     * 子类又需要的时候需要重写
     * (non-PHPdoc)
     * @see Serializable::serialize()
     */
    public function serialize(){return '';}
    /**
     * 子类又需要的时候需要重写
     * (non-PHPdoc)
     * @see Serializable::unserialize()
     */
    public function unserialize($s){}
    public function __call($method, $args) {
        Utils::log(get_class($this).':'.$method.' 方法不存在');
    }
    /**
     * 前置方法
     */
    protected function _before_run(){
        Utils::statistics('begin');
        Utils::log(get_class($this).' [--START--]');
    }
    /**
     * 后置方法
     */
    protected function _after_run(){
        Utils::statistics('end');
        Utils::log(get_class($this).' [--END--][RunTime:'.Utils::statistics('begin','end',6).'s]');
    }
    /**
     * 任务入口
     * 调用子类的run方法
     */
    public function main(){
        $this->_before_run();
        $this->run();
        $this->_after_run();
    }
    
}