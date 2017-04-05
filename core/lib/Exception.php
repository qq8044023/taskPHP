<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/TimePhp
 */
namespace core\lib;
/**
 * 默认系统异常处理
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class Exception extends \Exception {
	/**
	 * 创建新的抛出异常.
	 *  throw new Exception('Something went terrible wrong');
	 * @param   string          $message    错误消息
	 * @param   array           $variables  变量
	 * @param   integer|string  $code       异常代码
	 * @param   Exception       $previous   
	 * @return  void
	 */
	public function __construct($message = "", $code = 0, \Exception $previous = NULL)
	{
		// 将消息和整数代码传递给父类
		parent::__construct($message, (int) $code, $previous);
		// 保存未修改代码
		$this->code = $code;
		Log::input($message,1);
	}
}