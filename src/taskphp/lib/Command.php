<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
namespace taskphp;
/**
 * 命令操作类
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class Command{
    public static $_argv=null;
    
    public static $_cmd='start.php';
    /**
     * 当前的命令名称
     * 默认为启动
     * @var string
     */
    public static $_cmd_key='start';
    /**
     * 当前的命令的参数值
     * @var string
     */
    public static $_cmd_value='all';
    /**
     * 允许的命令
     * //命令 => 参数值  true代表有参数  false没有参数
     * @var array 
     */
    public static $_cmd_list=[
        'start'=>true,  //启动任务
        'close'=>true,   //关闭
        'restart'=>true,  //重载任务
        'distribute'=>false,//分配任务
        'worker'=>true,//运行任务
    ];
    
    public static function setArgv($argv){
        self::$_argv=$argv;
    }
    
    /**
     * 分解命令
     */
    public static function analysis(){
        if(!count(self::$_argv)){
            return;
        }
        $argv=self::$_argv;
        self::$_cmd=array_shift($argv);
        foreach($argv as &$item){
            $item=trim($item);
            if(isset($is_value)){
                self::$_cmd_value=$item;
                break;
            }
            if($item){
                self::$_cmd_key=$item;
            }
            $is_value=true;
        }
    } 
}