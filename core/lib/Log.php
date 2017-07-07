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
    private static $_handle= array();
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
     * @param data $data 欲写入的数据
     * @param int $type 日志等级 -1:无等级  0:DEBUG调试 1:INFO正常  2:WARN警告 3:ERROR错误 4:FATAL致命错误   默认0
     */
    public static function input($data,$type=0){
        $filename=date("Y-m-d").self::$_ext;
        self::initDir(self::$_logPath);
        if(!isset(self::$_handle[self::$_logPath.DS.$filename])){
            self::$_handle[self::$_logPath.DS.$filename] = @fopen(self::$_logPath.DS.$filename, 'a');
        }
        $desctitle=($type==-1)?'':'['.self::getDescTitle($type).']:';
        $fineStamp = date('Y-m-d H:i:s') . substr(microtime(), 1, 9);
        fwrite(self::$_handle[self::$_logPath.DS.$filename],strtoupper('['.$fineStamp.']').$desctitle.self::getRequest($data));
    }

    /**
     * 覆盖写入内容
     *
     */
    
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
        $title='DEBUG';
        switch($type){
            case 1:
                $title='INFO';
                break;
            case 2:
                $title='WARN';
                break;
            case 3:
                $title='ERROR';
                break;
            case 4:
                $title='FATAL';
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
    public function close($val){
        @fclose(self::$_handle[$val]);
    }
}