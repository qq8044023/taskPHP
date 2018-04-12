<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
namespace taskphp;
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
    public function __construct($message = "", $code = 0, \Exception $previous = NULL){
        if($message == "")return;
		// 将消息和整数代码传递给父类
		parent::__construct($message, (int) $code, $previous);
		// 保存未修改代码
		$this->code = $code;
		$this->file==null && $this->file='';
		$this->line==null && $this->line='';
		$this->handler($code,$message,$this->file,$this->line);
	}
	
	/**
     * 注册错误拦截函数到系统中
     * 
     * @return void
     */
    public function register(){
        set_error_handler(array($this, 'handler'),E_ALL );
		set_exception_handler(array($this, 'message'));
		register_shutdown_function(array($this, 'fatal'));
    }
    /**
     * 自定义异常理
     * @param unknown $e
     */
    public function message($e){
        $this->handler(E_ERROR,$e->getMessage(),$e->getFile(),$e->getLine());
    }
    /**
     * 致命错误处理
     */
    public function fatal() {
        if ( function_exists( 'error_get_last' ) ) {
            if ( $e = error_get_last() ) {
                $error = $e['message'];
                $file  = $e['file'];
                $line  = $e['line'];
                $this->handler( $e['type'], $error, $file, $line );
                exit;
            }
        }
    }
    /**
     * 错误处理
     * @param unknown $error_level
     * @param unknown $error_message
     * @param unknown $file
     * @param unknown $line
     */
	public function handler($error_level,$error_message, $file,  $line) {
	    if(Utils::config('log.error')){
	        $message=sprintf("[%s]:%s in %s on line %d",$this->errorType($error_level), trim($error_message),  $file, $line);
	        Utils::log($message,-1);
	    }
	}
	/**
	 * 获取错误标识
	 *
	 * @param $type
	 *
	 * @return string
	 */
	public function errorType($type) {
	    switch ( $type ) {
	        case E_ERROR: // 1
	            return 'ERROR';
	        case E_WARNING: // 2
	            return 'WARNING';
	        case E_PARSE: // 4
	            return 'PARSE';
	        case E_NOTICE: // 8
	            return 'NOTICE';
	        case E_CORE_ERROR: // 16
	            return 'CORE_ERROR';
	        case E_CORE_WARNING: // 32
	            return 'CORE_WARNING';
	        case E_COMPILE_ERROR: // 64
	            return 'COMPILE_ERROR';
	        case E_COMPILE_WARNING: // 128
	            return 'COMPILE_WARNING';
	        case E_USER_ERROR: // 256
	            return 'USER_ERROR';
	        case E_USER_WARNING: // 512
	            return 'USER_WARNING';
	        case E_USER_NOTICE: // 1024
	            return 'USER_NOTICE';
	        case E_STRICT: // 2048
	            return 'STRICT';
	        case E_RECOVERABLE_ERROR: // 4096
	            return 'RECOVERABLE_ERROR';
	        case E_DEPRECATED: // 8192
	            return 'DEPRECATED';
	        case E_USER_DEPRECATED: // 16384
	            return 'USER_DEPRECATED';
	    }
	    return $type;
	}
}