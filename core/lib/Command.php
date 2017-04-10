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
    public static $_cmd_value='';
    /**
     * 允许的命令
     * //命令 =》参数值  true代表有参数  false没有参数
     * @var array 
     */
    private static $_cmd_list=array(
        'start'=>false,  //启动
        'close'=>false,   //关闭
        'reload'=>false,  //重载任务
        'select'=>false,  //列出任务
        'delete'=>true,   //删除任务  需要带参数值
        'exec'=>true,   //运行任务  需要参数
    );
    /**
     * 分解命令
     */
    public static function analysis(){
        $argv=@$argv?$argv:$_SERVER['argv'];
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
    /**
     * 合法验证
     */
    public static function check_legal(){
        if(!isset(self::$_cmd_list[self::$_cmd_key])){
            $keys=array_keys(self::$_cmd_list);
            $keys_string=implode('|', $keys);
            Ui::displayUI("Usage: php ".$_SERVER['argv'][0]." {".$keys_string."}");
        }
        if(!method_exists(new static,self::$_cmd_key)){
            Ui::displayUI("Usage: php ".$_SERVER['argv'][0]." {".$keys_string."}");
        }
    }
    /**
     * 入口
     */
	public static function run(){
	    self::analysis();
	    self::check_legal();
	    $foo=self::$_cmd_key;
	    self::$foo();
	}
	/**
	 * 启动
	 */
	public static function start(){
	    $Daemon= new Daemon();
	    $Daemon->init();
	}
	/**
	 * 关闭 
	 */
	public static function close(){
	     $is_daemon=Utils::is_daemon(array());
	     $is_daemon=$is_daemon?'yes':'no';
	     Ui::displayUI('runing:'.$is_daemon,false);
         foreach (Log::getPidAll() as $v) !is_null($v) && posix_kill($v, SIGTERM);//关闭当前进程
         Log::inputCover();
         Ui::displayUI('close ok');
	}
	
	/**
	 * 重载任务
	 */
    static public function reload(){
        $TaskManage = new TaskManage();
        $TaskManage->load_worker();
        Ui::displayUI('task reload ok');
    }
    
    /**
     * 列出任务
    */
    static public function select(){
        $TaskManage = new TaskManage();
        $message='';
        //获取待执行任务列表
        foreach ($TaskManage->run_worker_list() as $v){
            $worker=$v->get_worker();
            $message.= "task_name:".$worker->get_name().PHP_EOL;
            $message.= "run_time:".Utils::timer_to_string($worker->get_timer()).PHP_EOL;
            $message.= "next_time:".date("Y-m-d H:i:s",$v->get_run_time()).PHP_EOL;
        }
        Ui::displayUI($message);
    }
    
    /**
     * 删除任务
     */
    public static function delete(){
        if(!self::$_cmd_value){
            Ui::displayUI('specify the name of the task to delete');
        }
        $TaskManage = new TaskManage();
        $TaskManage->del_worker(self::$_cmd_value);
        Ui::displayUI(self::$_cmd_value. ' delete ok');
    }
    /**
     * 执行任务
     */
    public static function exec(){
        if(!self::$_cmd_value){
            Ui::displayUI('specify the name of the task to exec');
        }
        $taskManage=new TaskManage();
        $taskManage->run_task(self::$_cmd_value);
    }
}