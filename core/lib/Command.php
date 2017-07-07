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
    public static $_cmd_key='help';
    /**
     * 当前的命令的参数值
     * @var string
     */
    public static $_cmd_value='';
    /**
     * 允许的命令
     * //命令 => 参数值  true代表有参数  false没有参数
     * @var array 
     */
    public static $_cmd_list=array(
        'help'=>false,  //帮助
        'start'=>false,  //启动
        'close'=>false,   //关闭
        'reload'=>false,  //重载任务
        'select'=>false,  //列出任务
        'delete'=>true,   //删除任务  需要带参数值
        'exec'=>true,   //运行任务  需要参数
    );
    /**
     * 获取允许的命令
     * @return array
     */
    public static function get_cmd_list(){
        return self::$_cmd_list;
    }
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
    /**
     * 合法验证
     */
    public static function check_legal(){
        if(!isset(self::$_cmd_list[self::$_cmd_key]) || !method_exists(new static,self::$_cmd_key)){
            self::help();
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
     *帮助
     */
    private static function help(){
        $text='Usage: php '.$_SERVER['argv'][0].'<command> [options]'.PHP_EOL;
        $text .= 'Available commands: '.PHP_EOL;
        foreach (self::$_cmd_list as $key=>$val){
            if(__FUNCTION__ ==$key)continue;
            if($val){
                $text.='  '.$key.' [options]'.PHP_EOL;
            }else{
                $text.='  '.$key.PHP_EOL;
            }
        }
        Ui::displayUI($text,false);
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
	     $is_daemon=Daemon::is_daemon(array());
	     $is_daemon=$is_daemon?'yes':'no';
	     Ui::displayUI('runing:'.$is_daemon,false);
	     $message='';
	     if($is_daemon){
	       foreach (Daemon::$_sys_pids as &$pid){
	           $message=$message.'pid:'.$pid.' close ok'.PHP_EOL;
	       }
	       Ui::displayUI($message,false);
	       foreach (Daemon::$_sys_pids as &$pid){
	           if(Utils::get_os()=='win'){
	               system('taskkill /f /t /im php.exe');
	           }else{
	               posix_kill($pid, SIGTERM);
	               system('kill -9 '.$pid);
	           }
	       }
	     }
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
        Ui::statusTasklist($TaskManage->run_worker_list());
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
        ini_set('memory_limit',Config::get('memory_limit'));
        $taskManage=new TaskManage();
        $taskManage->run_task(self::$_cmd_value);
    }
    
}