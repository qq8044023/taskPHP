<?php
//系统配置
return array(
    //指定用户  nobody  www
    'core_user'         =>'nobody',
    //运行多少次后重启,设置true,将不用子进程执行任务[效率高点,但设置任务将需要重启服务],默认为1 每次都使用子进程执行任务[设置任务立即生效]
    'runer_limit'       =>true,  
    //指定任务进程最大内存
    'memory_limit'      =>512,
    //任务列表
    'task_list'=>array(
        //demo任务 
        'demo'=>array(
            //class名称,(设置true或者不设置此参数)代表tasks目录里面的任务会自动找到该任务的class名称,非tasks目录里面的任务则填写完整的class名称core\lib\xxxx
            'class_name'=>true,   
            //crontad格式 :秒 分 时 天 月 年 周
            'timer'     =>'/5 * * * * * *', 
        ),
    ),
    /**
     * 数据库配置
     *   */
    'DB'=>array(
        'db_type'       =>'MYSQL',//数据库类型
        'db_host'       =>'127.0.0.1',//地址
        'db_username'   =>'root',//账户
        'db_password'   =>'',//密码
        'db_prot'       =>'3306',//端口
        'db_name'       =>'dbname'//选中的数据库
    ),
);