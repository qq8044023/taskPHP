<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace core\lib;
/**
 * ThinkPHP 数据库中间层实现类
 */
class Db {

    static private  $instance   =  array();     //  数据库连接实例
    static private  $_instance  =  null;   //  当前数据库连接实例

    /**
     * 取得数据库类实例
     * @static
     * @access public
     * @param mixed $config 连接配置
     * @return Object 返回数据库驱动类
     */
    static public function getInstance($config=array()) {
        $md5    =   md5(serialize($config));
        if(!isset(self::$instance[$md5])) {
            // 解析连接参数 支持数组和字符串
            $options    =   self::parseConfig($config);
            // 兼容mysqli
            if('mysqli' == $options['type']) $options['type']   =   'mysql';
            // 如果采用lite方式 仅支持原生SQL 包括query和execute方法
            $class  =   $options['lite']?  'core\lib\db\Lite' :   'core\\lib\\db\\Driver\\'.ucwords($options['type']);
            if(class_exists($class)){
                self::$instance[$md5]   =   new $class($options);
            }else{
                // 类没有定义
                throw new Exception("not sb driver:".$class);
            }
        }
        self::$_instance    =   self::$instance[$md5];
        return self::$_instance;
    }

    /**
     * 数据库连接参数解析
     * @static
     * @access private
     * @param mixed $config
     * @return array
     */
    static private function parseConfig($config){
        if(!empty($config)){
            if(is_string($config)) {
                return self::parseDsn($config);
            }
            $config =   array_change_key_case($config);
            $config = array (
                'type'          =>  isset($config['db_type'])?$config['db_type']:null,
                'username'      =>  isset($config['db_user'])?$config['db_user']:null,
                'password'      =>  isset($config['db_pwd'])?$config['db_pwd']:null,
                'hostname'      =>  isset($config['db_host'])?$config['db_host']:null,
                'hostport'      =>  isset($config['db_port'])?$config['db_port']:null,
                'database'      =>  isset($config['db_name'])?$config['db_name']:null,
                'dsn'           =>  isset($config['db_dsn'])?$config['db_dsn']:null,
                'params'        =>  isset($config['db_params'])?$config['db_params']:null,
                'charset'       =>  isset($config['db_charset'])?$config['db_charset']:'utf8',
                'deploy'        =>  isset($config['db_deploy_type'])?$config['db_deploy_type']:0,
                'rw_separate'   =>  isset($config['db_rw_separate'])?$config['db_rw_separate']:false,
                'master_num'    =>  isset($config['db_master_num'])?$config['db_master_num']:1,
                'slave_no'      =>  isset($config['db_slave_no'])?$config['db_slave_no']:'',
             //   'debug'         =>  isset($config['db_debug'])?$config['db_debug']:APP_DEBUG,
                'lite'          =>  isset($config['db_lite'])?$config['db_lite']:false,
            );
        }else {
            $config = array (
                'type'          =>  Utils::dbConfig('DB_TYPE'),
                'username'      =>  Utils::dbConfig('DB_USER'),
                'password'      =>  Utils::dbConfig('DB_PWD'),
                'hostname'      =>  Utils::dbConfig('DB_HOST'),
                'hostport'      =>  Utils::dbConfig('DB_PORT'),
                'database'      =>  Utils::dbConfig('DB_NAME'),
                'dsn'           =>  Utils::dbConfig('DB_DSN'),
                'params'        =>  Utils::dbConfig('DB_PARAMS'),
                'charset'       =>  Utils::dbConfig('DB_CHARSET'),
                'deploy'        =>  Utils::dbConfig('DB_DEPLOY_TYPE'),
                'rw_separate'   =>  Utils::dbConfig('DB_RW_SEPARATE'),
                'master_num'    =>  Utils::dbConfig('DB_MASTER_NUM'),
                'slave_no'      =>  Utils::dbConfig('DB_SLAVE_NO'),
                'debug'         =>  true,
                'lite'          =>  Utils::dbConfig('DB_LITE'),
            );
        }
        return $config;
    }

    /**
     * DSN解析
     * 格式： mysql://username:passwd@localhost:3306/DbName?param1=val1&param2=val2#utf8
     * @static
     * @access private
     * @param string $dsnStr
     * @return array
     */
    static private function parseDsn($dsnStr) {
        if( empty($dsnStr) ){return false;}
        $info = parse_url($dsnStr);
        if(!$info) {
            return false;
        }
        $dsn = array(
            'type'      =>  $info['scheme'],
            'username'  =>  isset($info['user']) ? $info['user'] : '',
            'password'  =>  isset($info['pass']) ? $info['pass'] : '',
            'hostname'  =>  isset($info['host']) ? $info['host'] : '',
            'hostport'  =>  isset($info['port']) ? $info['port'] : '',
            'database'  =>  isset($info['path']) ? substr($info['path'],1) : '',
            'charset'   =>  isset($info['fragment'])?$info['fragment']:'utf8',
        );
        
        if(isset($info['query'])) {
            parse_str($info['query'],$dsn['params']);
        }else{
            $dsn['params']  =   array();
        }
        return $dsn;
     }

    // 调用驱动类的方法
    static public function __callStatic($method, $params){
        return call_user_func_array(array(self::$_instance, $method), $params);
    }
}
