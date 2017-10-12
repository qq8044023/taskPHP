## 当前版本 2.0

## 历史版本
-->[taskPHP1.0](https://github.com/qq8044023/taskPHP/tree/1.0)<br>

## taskPHP
taskPHP基于php开发的定时计划任务框架,利用多进程实现任务的分配和运行,多种进程间通信驱动支持,支持多线程模式需要安装pthreads扩展(可选),支持linux和windows。有较好的伸缩性、扩展性、健壮稳定性而被多家公司使用，同时也希望开源爱好者一起贡献。<br>
## 项目地址
github地址: https://github.com/qq8044023/taskPHP<br>
oschina地址: http://git.oschina.net/cqcqphper/taskPHP<br>
这两个地址都会同步更新。
## 在线交流QQ群
如感兴趣请加QQ群 一起探讨、完善。越多人支持,就越有动力去更新,喜欢记得右上角star哈。<br>
<a target="_blank" href="//shang.qq.com/wpa/qunwpa?idkey=2a8520f5c1518df3a796e71d8c993b2f00856a035d59ca46285c4e325116ba4d"><img border="0" src="//pub.idqqimg.com/wpa/images/group.png" alt="taskPHP框架交流群" title="taskPHP框架交流群">taskPHP ①群:375841535（空）</a>

## 框架概况
框架目录结构:
``` php
taskPHP								根目录
|-- core							框架系统目录
|   |-- lib							框架核心文件目录
|   |   |-- ....					众多的框架核心类库文件
|   |-- guide.php					框架引导文件
|   |-- distribute_listen.php		任务派发进程入口
|   |-- worker_listen.php			任务执行进程入口
|-- docs							开发文档存放目录
|-- logs							日志目录
|-- tasks							用户任务目录
|   |-- demo						demo任务
|	|	|-- Lib						demo任务的扩展目录
|	|	|-- demo.php			demo任务类文件
|	|	...							更多任务
|   |-- config.php					全局配置文件
|-- main.php						框架入口文件
|-- windows.cmd				windows快速启动文件
``` 
框架说明
1. 任务多进程运行模式。
2. 任务多线程模式,需要安装pthreads扩展(可选)。
3. 多种进程通信方式堵塞式消息队列。
4. 任务派发及具体任务执行不在同个进程[distribute_listen.php]和[worker_listen.php],windows和linux下启用入口文件[main.php],windows下可运行[windows_single.cmd]快速启动。
5. 执行时间语法跟crontab类似,且支持秒设置。
6. 添加任务简单,只需继承Task基类,实现任务入口run方法。

## 环境要求
1. php版本>= 5.5<br>
2. 开启socket扩展<br>
   
## 注意事项
1. 由于任务存在派发时间，所以任务运行的时间可能会有1-2秒的误差。
2. 编写任务有问题或调用exit将导致后台脚本停止,需要通过远控管理器重启进程。
3. 多线程模式运行一段时间后报错,pthreads has detected that the core\lib\Pthread could not be started, the system lacks the necessary resources or the system-imposed limit would be exceeded in xxx
4. 后台任务数量多或者任务运行时间很密集导致数据库链接过多没有释放,需要再任务结尾处执行数据库链接对象的close方法来关闭链接。

## 文档列表
-->[数据库类使用教程 支持(Mysql,Mongo,Oracle,Pgsql,Sqlsrv,Sqllite)](./docs/mysql.md)<br>
-->[windows下安装php多线程扩展pthreads教程](./docs/thread_windows.md)<br>
-->[工具类Utils使用说明](./docs/utils.md)<br>
## 使用说明

### composer安装taskphp框架:
``` php
composer require taskphp/taskphp dev-master
```
## Windows 命令操作
### 调试启动程序
```
D:\phpStudy\wwwroot\ostaskphp>php main.php start
------------------------- taskPHP ------------------------------
taskPHP version:2.0      PHP version:5.6.1
------------------------- taskPHP PROCESS ----------------------
listen                      processes                     status
distribute                    1                            [OK]
demo                          2                            [OK]
demo2                         2                            [OK]
----------------------------------------------------------------
taskPHP:demo task load complete
taskPHP is running..............
```


## Liunx 命令操作
### 调试启动程序
``` php
[root@FX-DEBUG taskphps]# php ./main.php start
------------------------- taskPHP ------------------------------
taskPHP version:2.0      PHP version:5.6.1
------------------------- taskPHP PROCESS ----------------------
listen                      processes                     status
distribute                    1                            [OK]
demo                          2                            [OK]
demo2                         2                            [OK]
----------------------------------------------------------------
taskPHP:demo task load complete
taskPHP is running..............

``` 
### 后台启动程序

``` php
[root@FX-DEBUG taskphps]# php ./main.php start &
------------------------- taskPHP ------------------------------
taskPHP version:2.0      PHP version:5.6.1
------------------------- taskPHP PROCESS ----------------------
listen                      processes                     status
distribute                    1                            [OK]
demo                          2                            [OK]
demo2                         2                            [OK]
----------------------------------------------------------------
taskPHP:demo task load complete
taskPHP is running..............
```


### 时间配置格式说明:

``` php
   * * * * * * *    //格式 :秒 分 时 天 月 年 周
  10 * * * * * *    //表示每一分钟的第10秒运行
 /10 * * * * * *	//表示每10秒运行
 /1 * 15,16 * * * * //表示 每天的15点,16点的每一秒运行
``` 
