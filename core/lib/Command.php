<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
namespace core\lib;
/**
 * 命令操作类
 * @author cqcqphper 小草<cqcqphper@163.com>
 *
 */
class Command{
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
    ];
    /**
     * 分解命令
     */
    public static function analysis(){
        $argv=$_SERVER['argv'];
        array_shift($argv);
        if(!count($argv)){
            return;
        }
        
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