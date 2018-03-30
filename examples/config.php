<?php
//系统配置
return [
    //任务列表
    'task_list'=>[
        'examples\\Demo1'=>[
            //指定任务进程最大内存  系统默认为512M
            'worker_memory'      =>'1024M',
            //开启任务进程的多线程模式
            'worker_pthreads'   =>false,
            //任务的进程数 系统默认1
            'worker_count'=>1,
            //crontad格式 :秒 分 时 天 月 年 周
            'timer'     =>'/5 * * * * * *',
        ], 
        'examples\\Demo2'=>[
            //指定任务进程最大内存  系统默认为512M
            'worker_memory'      =>'1024M',
            //开启任务进程的多线程模式
            'worker_pthreads'   =>false,
            //任务的进程数 系统默认1
            'worker_count'=>1,
            //crontad格式 :秒 分 时 天 月 年 周
            'timer'     =>'/20 * * * * * *',
        ],
    ],
    'db'=>[
        'type'          =>  'mysql',
        'username'      =>  'root',
        'password'      =>  'root',
        'host'      =>  '127.0.0.1',
        'port'      =>  '3306',
        'name'      =>  'test',
        // 数据库编码默认采用utf8
        'charset'       => 'utf8',
        // 数据库表前缀
        'prefix'        => 'test_',
        // 开启断线重连
        'break_reconnect'=>true,
    ],
    
];