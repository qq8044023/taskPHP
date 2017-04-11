<?php
/**
 * UI显示 
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 *   */
namespace core\lib;
class Ui{
    /**
     * 查看 启动状态UI
     *   */
    public static function statusUI(){
        $text= "------------------------- taskPHP ------------------------------".PHP_EOL;
        $text.= 'taskPHP version:' . ML_VERSION . "      PHP version:".PHP_VERSION.PHP_EOL;
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
            $text.= str_pad($key, 30).'N'. str_pad('', 27). " [OK] ".PHP_EOL;
        }
        $text.= "----------------------------------------------------------------";
        self::displayUI($text,false);
    }
    
    public static function statusTasklist($list){
        $text='';
        $text.= "------------------------ taskPHP task_list ---------------------".PHP_EOL;
        $text.= "task_name".str_pad('', 14). "run_time".str_pad('', 21)."next_time".PHP_EOL;
        foreach ($list as $item){
            $worker=$item->get_worker();
            $text.= str_pad($worker->get_name(), 20).Utils::timer_to_string($worker->get_timer()). str_pad('', 10). date("Y-m-d H:i:s",$item->get_run_time()).PHP_EOL;
        }
        $text.= "----------------------------------------------------------------";
        self::displayUI($text,false);
    }
    /**
     * 默认UI
     * @param unknown $text
     * @param string $isClose  */
    public static function displayUI($text,$isClose=true){
        $text=$text.PHP_EOL;
        echo $text;
        $isClose==true && die;
    }
}



