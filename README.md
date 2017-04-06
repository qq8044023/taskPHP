## taskPHP
taskPHP基于原生态php开发的定时计划任务框架,利用多进程实现任务的分配和运行,利用原生态php内存共享实现进程间通信,支持linux和windows。有较好的伸缩性、扩展性、健壮稳定性而被多家公司使用，同时也希望开源爱好者一起贡献。<br>
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
|-- logs							日志目录
|-- tasks							用户任务目录
|   |-- demo						demo任务
|	|	|-- Lib						demo任务的扩展目录
|	|	|-- demoTask.php			demo任务类文件
|	|	|-- config.php				demo任务配置文件
|	|	...							更多任务
|   |-- config.php					全局配置文件
|-- main.php						框架入口文件
|-- windows_single.cmd				windows快速启动文件
``` 
框架说明
1. linux下子进程执行任务,修改脚本无需重启后台服务立即生效,windows下修改任务脚本后需重启后台脚本 但往系统添加执行不受影响
2. 使用内存共享实现进程通信，堵塞式消息队列,整个框架的运行无需第三方扩展。
3. 任务派发及具体任务执行不在同个进程[distribute_listen.php]和[worker_listen.php],windows和linux下启用入口文件[main.php],windows下可运行[windows_single.cmd]快速启动
4. 执行时间语法跟crontab类似实现crontab的运行规则,并有辅助工具在Utils类,且支持秒设置.
5. 添加任务简单,只需继承Task基类,实现任务入口run方法

## 注意事项
1. 由于任务存在派发时间，所以任务运行的时间可能会有1-2秒的误差。
2. windows下执行任务在循环里,编写任务有问题或调用exit将导致后台脚本停止,linux下无此问题。

## 使用说明

### 时间配置格式说明:

``` php
   * * * * * * *    //格式 :秒 分 时 天 月 年 周
  10 * * * * * *    //表示每一分钟的第10秒运行
 /10 * * * * * *	//表示每10秒运行
``` 

### 系统命令说明:
``` php
mian.php  [start]  启动 可不带参数
mian.php  close  结束
main.php  reload  重新加载任务
main.php  delete demo   删除任务
main.php  select  查看任务列表
``` 
### composer安装taskphp框架:
``` php
composer require taskphp/taskphp
```

### Windows 命令操作
## 调试启动程序
```
D:\phpStudy\wwwroot\ostaskphp>php main.php
------------------------- taskPHP ------------------------------
taskPHP version:1.0      PHP version:5.6.1
------------------------- taskPHP PROCESS ----------------------
listen                      processes                     status
distribute                    N                            [OK]
worker                        N                            [OK]
----------------------------------------------------------------
```

## 结束程序
``` php

D:\phpStudy\wwwroot\ostaskphp>php main.php close
runing:no
close ok

```
## 重新加载任务

``` php
D:\phpStudy\wwwroot\ostaskphp>php ./main.php reload
taskPHP:demo task load complete
taskPHP is running..............
task reload ok
```

## 删除任务

``` php

D:\phpStudy\wwwroot\ostaskphp>php ./main.php delete demo
taskPHP:demo task load complete
taskPHP is running..............
task reload ok
```
## 查看任务列表

``` php
D:\phpStudy\wwwroot\ostaskphp>php ./main.php select
task_name:demo
run_time:1 * * * * * *
next_time:2017-04-06 10:08:01
```

### Liunx 命令操作
## 调试启动程序
``` php
[root@FX-DEBUG taskphps]# php ./main.php start
------------------------- taskPHP ------------------------------
taskPHP version:1.0      PHP version:5.6.9
------------------------- taskPHP PROCESS ----------------------
listen                      processes                     status
distribute                    N                            [OK] 
worker                        N                            [OK] 
----------------------------------------------------------------
taskPHP:demo task load complete
taskPHP is running..............

``` 
## 后台启动程序

``` php
[root@FX-DEBUG taskphps]# php ./main.php start &
------------------------- taskPHP ------------------------------
taskPHP version:1.0      PHP version:5.6.9
------------------------- taskPHP PROCESS ----------------------
listen                      processes                     status
distribute                    N                            [OK] 
worker                        N                            [OK] 
----------------------------------------------------------------
taskPHP:demo task load complete
taskPHP is running..............
```
## 结束程序
``` php

[root@FX-DEBUG taskphps]# php ./main.php close
runing:no
close ok

```

## 重新加载任务

``` php
[root@FX-DEBUG taskphps]# php ./main.php reload
taskPHP:demo task load complete
taskPHP is running..............
task reload ok
```

## 删除任务

``` php
[root@FX-DEBUG taskphps]# php ./main.php  delete demo
taskPHP:demo task load complete
taskPHP is running..............
task reload ok
```
## 查看任务列表

``` php
[root@FX-DEBUG taskphps]# php ./main.php select
task_name:demo
run_time:1 * * * * * *
next_time:2017-04-06 10:08:01

```

### Mysql数据库操作

## 添加
``` php

```
## 删除
``` php

```
## 修改
``` php

```
## 查询单条
``` php

```
## 查询多条
``` php

```
## 查询总数
``` php

```
## where条件
``` php

```
## in
``` php

```
## group by
``` php

```
## left join
``` php

```
## 执行底层sql操作
``` php

```

