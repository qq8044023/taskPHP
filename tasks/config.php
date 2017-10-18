<?php
//系统配置
return [
    //系统队列配置
    'queue'=>[
        'drive'         => 'Sqlite',//驱动类型 Sqlite|Redis|Mysql|Shm
    ],
   
    //web管理配置
    //内网访问地址：http://127.0.0.1:8082
    //外网访问地址：http://ip:8082
    'web_manage'=>[
        //ip地址
        'address'=>'0.0.0.0',
        //端口
        'port'=>8082,
    ],
    //任务列表
    'task_list'=>[
        'demo'=>[
            //指定任务进程最大内存  系统默认为512M
            'worker_memory'      =>'1024M',
            //开启任务进程的多线程模式
            'worker_pthreads'   =>false,
            //任务的进程数 系统默认1
            'worker_count'=>1,
            //crontad格式 :秒 分 时 天 月 年 周
            'timer'     =>'/10 * * * * * *',
        ],
    ],
    'db'=>[
        'type'          =>  'mysql',
        'username'      =>  '',
        'password'      =>  '',
        'hostname'      =>  '',
        'hostport'      =>  '3306',
        'database'      =>  '',
        // 数据库连接参数
        'params'        => [],
        // 数据库编码默认采用utf8
        'charset'       => 'utf8',
        // 数据库表前缀
        'prefix'        => 'tourism_',
        // 数据库调试模式
        'debug'         => false,
        // 开启断线重连
        'break_reconnect'=>true,
    ],
];