<?php
//系统配置
return [
    //任务列表
    'task_list'=>[
        //key为任务名，多任务下名称必须唯一
        'demo1'=>[
            'callback'=>['examples\\Demo1','run'],//任务调用:类名和方法
            'worker_memory'      =>'1024M',//指定任务进程最大内存  系统默认为512M
            'worker_pthreads'   =>false,//开启任务进程的多线程模式
            'worker_count'=>1,//任务的进程数 系统默认1
            'crontab'     =>'/5 * * * * * *',//crontad格式 :秒 分 时 天 月 年 周
        ], 
        //key为任务名，多任务下名称必须唯一
        'demo2'=>[
            'callback'=>['examples\\Demo2','run'],//任务调用:类名和方法
            'worker_memory'      =>'1024M',//指定任务进程最大内存  系统默认为512M
            'worker_pthreads'   =>false,//开启任务进程的多线程模式
            'worker_count'=>1,//任务的进程数 系统默认1
            'crontab'     =>'/20 * * * * * *',//crontad格式 :秒 分 时 天 月 年 周
        ],
    ],
    //'php_path'=>'php',//可手动为php设置环境变量
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