taskPHP 3.0 —— 你值得信赖的PHP定时计划任务框架
===============

## 历史版本
-->[taskPHP1.x](https://gitee.com/cqcqphper/taskPHP/tree/taskPHP/1.0)<br>
-->[taskPHP2.x](https://gitee.com/cqcqphper/taskPHP/tree/taskPHP/2.1)<br>
> 所有分支 [查看所有分支](https://gitee.com/cqcqphper/taskPHP/branches)

## taskPHP
taskPHP基于php开发的定时计划任务框架,多进程实现任务的分配和运行,多种进程间通信驱动支持,支持多线程模式需要安装pthreads扩展(可选),支持linux和windows。有较好的伸缩性、扩展性、健壮稳定性而被多家公司使用，同时也希望开源爱好者一起贡献。<br>
## 项目地址
github地址: https://github.com/qq8044023/taskPHP<br>
gitee地址: https://gitee.com/cqcqphper/taskPHP<br>
这两个地址都会同步更新。
## 在线交流QQ群
如感兴趣请加QQ群 一起探讨、完善。越多人支持,就越有动力去更新,喜欢记得右上角star哈。<br>
<a target="_blank" href="//shang.qq.com/wpa/qunwpa?idkey=2a8520f5c1518df3a796e71d8c993b2f00856a035d59ca46285c4e325116ba4d"><img border="0" src="//pub.idqqimg.com/wpa/images/group.png" alt="taskPHP框架交流群" title="taskPHP框架交流群">taskPHP ①群:375841535（空）</a>

框架说明
1. 任务多进程运行模式。
2. 任务多线程模式,需要安装pthreads扩展(可选)。
3. 多种进程通信方式堵塞式消息队列。
4. 任务派发及具体任务执行不在同个进程[distribute]和[worker],windows和linux下启用入口文件[start.php],windows下可运行[windows_start.cmd]快速启动。
5. 执行时间语法跟crontab类似,且支持秒设置。
``` php
   * * * * * * *    //格式 :秒 分 时 天 月 年 周
  10 * * * * * *    //表示每一分钟的第10秒运行
 /10 * * * * * *	//表示每10秒运行
 /1 * 15,16 * * * * //表示 每天的15点,16点的每一秒运行
```
6. 添加任务简单,只需编写任务类,实现任务入口run方法,详情参考examples目录内的测试任务。

## 环境要求
1. php版本>= 5.5<br>
2. 开启socket扩展<br>
3. 开启pdo扩展<br>
4. 开启shmop扩展<br>
   
## 注意事项
1. 由于任务存在派发时间，所以任务运行的时间可能会有1-2秒的误差。
2. 编写任务有问题或调用exit将导致后台脚本停止,需要通过远控管理器重启进程。
3. 多线程模式运行一段时间后报错,pthreads has detected that the taskphp\Pthread could not be started, the system lacks the necessary resources or the system-imposed limit would be exceeded in xxx
4. 后台任务数量多或者任务运行时间很密集导致数据库链接过多没有释放,需要再任务结尾处执行数据库链接对象的close方法来关闭链接。
5. 在windows下代码存放路径不能有空格，否则会导致进程启动不起来。php的环境变量也最好也不要有空格，如果有空格可在框架配置中定义数组项php_path='php'。

## 文档列表
-->[数据库类使用教程](./src/docs/db.md)<br>
-->[windows下安装php多线程扩展pthreads教程](./src/docs/thread_windows.md)<br>
-->[工具类Utils使用说明](./src/docs/utils.md)<br>
-->[thinkphp5.0框架的集成教程](./src/docs/thinkphp5.0.md)<br>


## 使用说明

### composer安装taskphp框架:
``` php
composer require taskphp/taskphp dev-master
```
## 命令操作
``` php
start.php  start [all|任务名]  启动 可不带参数默认all
start.php  start &   挂载后台运行,liunx操作
start.php  close all 结束框架  必带参数all

```

### 启动程序
``` php
[root@FX-DEBUG taskphps]# php ./start.php start
------------------------- taskPHP ------------------------------
taskPHP version:3.0      PHP version:5.5.38
license1:https://github.com/qq8044023/taskPHP
license2:https://gitee.com/cqcqphper/taskPHP
startTime:2018-04-04 10:00:50
------------------------- taskPHP Manage  ----------------------
http://ServerIp:8082
http://127.0.0.1:8082
------------------------- taskPHP PROCESS ----------------------
listen                      processes                     status
distribute                    1                          [success]
demo1                         1                          [success]
demo2                         1                          [success]
----------------------------------------------------------------
Press Ctrl-C to quit. Start success.
``` 