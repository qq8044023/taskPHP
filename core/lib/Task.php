<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
namespace core\lib;

/**
 * 任务接口
 * @author cqcqphper 小草<cqcqphper@163.com>
 */
interface SerializableBase extends \Serializable{

	/**
	 * 具体的执行
	 */
	public function run();

}
class Task implements SerializableBase{
    public function serialize(){
        return '';
    }
    public function unserialize($s){
    }
    public function run(){
        echo 'ok';
    }
    
}