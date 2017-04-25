<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
namespace core\lib;
/**
 * 日志类
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class Log{
    /**
     * 文件句柄
     * @var unknown
     */
    private static $_handle= NULL;
    /**
     * 日志后缀
     * @var string
     */
    private static $_ext='.log';
    /**
     * 日志目录
     * @var string
     */
    public static $_logPath=LOGS_PATH;

    /**
     * 写日志
     * @param unknown $data 欲写入的数据
     * @param int $type 日志等级 0正常 1错误 默认0
     */
    public static function input($data,$type=0){
        $filename=date("Y-m-d").'_'.self::getDescTitle($type).self::$_ext;
        self::initDir(self::$_logPath);
        if(self::$_handle == NULL){
            self::$_handle = @fopen(self::$_logPath.DS.$filename, 'a');
        }
        fwrite(self::$_handle,strtoupper(date('H:i:s').' '.self::getDescTitle($type)).':'.self::getRequest($data));
    }

    /**
     * 覆盖写入内容
     *   */
    
    /**
     * 将数组或者对象转换成字符串
     * @param unknown $request
     * @return string
     */
    private static function getRequest($request){
        if(!is_string($request)){
            $request=var_export($request,true);
        }
        return $request.PHP_EOL;
    }
    /**
     * 转换日志等级描述
     */
    private static function getDescTitle($type){
        $title='thing';
        switch($type){
            case 1:
                $title='error';
                break;
            default:
                break;
        }
        return $title;
    }
    /**
     * 初始化目录
     * @param unknown $dir
     * @return boolean
     */
    private static function initDir($dir){
        if (is_dir($dir) === false){
            if(!self::createDir($dir)){
                throw new Exception('Failed to create directory!');
                return false;
            }
        }
        return true;
    }
    /**
     * @abstract 创建目录
     * @param <type> $dir 目录名
     * @return bool
     */
    private static function createDir($dir){  
        return is_dir($dir) or (self::createDir(dirname($dir)) and @mkdir($dir, 0777));  
    }
    
    /**
     * 关闭文件句柄
     */
    public function close(){
        @fclose(self::$_handle);
    }
}