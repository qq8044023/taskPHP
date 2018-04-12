<?php
/**
 * 控制台输出
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 *   */
namespace taskphp;
class Console{
    /**
     * 输出头部信息
     **/
    public static function hreader(){
        $text= "------------------------- taskPHP ------------------------------".PHP_EOL;
        $text.= 'taskPHP version:' . TASKPHP_VERSION . "      PHP version:".PHP_VERSION.PHP_EOL;
        $text.= 'license1:https://github.com/qq8044023/taskPHP'.PHP_EOL;
        $text.= 'license2:https://gitee.com/cqcqphper/taskPHP'.PHP_EOL;
        $text.= 'start_time:'.date('Y-m-d H:i:s').PHP_EOL;
        $text.= 'web_manage:http://ip:8082'.PHP_EOL;
        $text.= "------------------------- process ------------------------------".PHP_EOL;
        $text.= "listen".str_pad('', 22). "processes".str_pad('', 21)."status";
        self::display($text,false);
    }
    /**
     * 输出进程列表
     * @param array $list
     */
    public static function process_list($list){
        $text='';
        foreach ($list as $key=>$val){
            $status='success';
            if(!$list[$key]['pid']){
                $status='fail';
            }
            $text.= str_pad($key, 30).$list[$key]['worker_count']. str_pad('', 25). " [".$status."] ".PHP_EOL;
        }
        $text.= "----------------------------------------------------------------".PHP_EOL;
        $text.= "Press Ctrl-C to quit. Start success.".PHP_EOL;
        self::display($text,false);
    }
    
    /**
     * 输出指定信息
     * @param string $text 内容
     * @param string $isClose  输出后是否退出
     */
    public static function display($text,$isClose=true){
        echo $text.PHP_EOL;
        $isClose==true && die;
    }
    /**
     * 输出运行日志
     * @param unknown $text
     */
    public static function log($text){
        self::display('['.date('H:i:s').']'.'[taskPHP]:'.$text,false);
    }
}