<?php
return array(
    /**
     * 数据库配置
     *   */
    'DB'=>array(
        'DB_TYPE'   => "mysql", // 数据库类型
        'DB_HOST'   => "127.0.0.1", // 服务器地址
        'DB_NAME'   => "tourism_game", // 数据库名
        'DB_USER'   => "root", // 用户名
        'DB_PWD'    => "root",  // 密码
        'DB_PORT'   => 3306, // 端口
        'DB_PREFIX' => "tourism_", // 数据库表前缀
        'DB_PARAMS'=>array('persist'=>true),//是否支持长连接
    ),
);