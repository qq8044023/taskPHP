<?php
/**
 * UI显示 
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 *   */
namespace taskphp;
class Ui{
    /**
     * 查看 启动状态UI
     *   */
    public static function statusUI(){
        $text= "------------------------- taskPHP ------------------------------".PHP_EOL;
        $text.= 'taskPHP version:' . TASKPHP_VERSION . "      PHP version:".PHP_VERSION.PHP_EOL;
        $text.= 'license1:https://github.com/qq8044023/taskPHP'.PHP_EOL;
        $text.= 'license2:https://gitee.com/cqcqphper/taskPHP'.PHP_EOL;
        $text.= 'startTime:'.date('Y-m-d H:i:s').PHP_EOL;
        $text.= "------------------------- taskPHP Manage  ----------------------".PHP_EOL;
        $text.='http://ServerIp:8082'.PHP_EOL;
        $text.='http://127.0.0.1:8082'.PHP_EOL;
        $text.= "------------------------- taskPHP PROCESS ----------------------".PHP_EOL;
        $text.= "listen".str_pad('', 22). "processes".str_pad('', 21)."status";
        self::displayUI($text,false);
    }
    /**
     * 进程ui输出
     * @param array $list
     */
    public static function statusProcess($list){
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
        self::displayUI($text,false);
    }
    
    public static function statusTasklist($list){
        $text='';
        $text.= "------------------------ taskPHP task_list ---------------------".PHP_EOL;
        $text.= "task_name".str_pad('', 14). "run_time".str_pad('', 21)."next_time".PHP_EOL;
        foreach ($list as $item){
            $worker=$item->get_worker();
            $text.= str_pad($worker->get_name(), 20).Crontab::crontab_to_string($worker->get_crontab()). str_pad('', 10). date("Y-m-d H:i:s",$item->get_run_time()).PHP_EOL;
        }
        $text.= "----------------------------------------------------------------";
        self::displayUI($text,false);
    }
    /**
     * 默认UI
     * @param string $text 内容
     * @param string $isClose  输出后是否退出
     */
    public static function displayUI($text,$isClose=true){
        echo $text.PHP_EOL;
        $isClose==true && die;
    }
    
    public static function showLog($text){
        echo '['.date('H:i:s').']'.'[taskPHP]:'.$text.PHP_EOL;
    }
}