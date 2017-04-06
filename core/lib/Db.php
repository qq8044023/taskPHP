<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/taskPHP
 */
namespace core\lib;
use core\lib\db\Mysql;
class Db{
    //配置文件
    private static $_config;
    public  $_query=null;
    //选中的表
    public function table($table=null){    
        switch (strtoupper(self::$_config["db_type"])){
            case "MYSQL":
                $this->_query=new Mysql($table, self::$_config);
                break;
            default:
                die("数据库扩展不存在..");
        }
        return $this->_query;
    }
    /**
     * 设置配置信息
     * @param unknown $config  */
    public static function setConfig($config){
        self::$_config=array(
            "db_type"=>$config["db_type"],
            "db_host"=>$config["db_host"],
            "db_username"=>$config["db_username"],
            "db_password"=>$config["db_password"],
            "db_prot"=>$config["db_prot"],
            "db_name"=>$config["db_name"]
        );
        return new static();
    }
}