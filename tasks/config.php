<?php
//系统配置
return array(
    
    'core_user'         =>'nobody',//指定用户  nobody  www
    'runer_limit'       =>true,    //运行多少次后重启,设置true,将不用子进程执行任务[效率高点,但设置任务将需要重启服务],默认为1 每次都使用子进程执行任务[设置任务立即生效]
    'memory_limit'      =>512,     //指定任务进程最大内存
    /**
     * 任务列表
     */
    'task_list'=>array(
        //demo任务 
        'demo'=>array(
            'class_name'=>true,         //class名称,设置true代表tasks目录里面的任务会自动找到该任务的class名称,非tasks目录里面的任务则填写完整的class名称core\lib\xxxx
            'timer'     =>'1 * * * * * *', //crontad格式 :秒 分 时 天 月 年 周
        ),
    ),
);