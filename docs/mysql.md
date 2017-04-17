## Mysql数据库操作

配置mysql
``` php
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
```

### 添加
``` php
<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\Config;
use core\lib\Db;
/**
 * 测试任务
 */
class demoTask extends Task{
	public function run(){
	    $db=Db::setConfig(Config::get('DB'));
	    $data=array(
            "player_id"=>1,
            "item_id"=>2,
            "rows"=>3
        );
        $db->table("表名")->add($data);
	   
	}
}

```
### 删除
``` php
<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\Config;
use core\lib\Db;
/**
 * 测试任务
 */
class demoTask extends Task{
	public function run(){
	    $db=Db::setConfig(Config::get('DB'));
	    $res=$db->table("表名")->where("id=1")->delete();
	    var_dump($res);
	}
}

```
### 修改
``` php
<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\Config;
use core\lib\Db;
/**
 * 测试任务
 */
class demoTask extends Task{
	public function run(){
	    $db=Db::setConfig(Config::get('DB'));
        $db->table("表名")->where(array("room_id"=>1))->save(array("status"=>1));
	}
}

```
### 查询单条
``` php
<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\Config;
use core\lib\Db;
/**
 * 测试任务
 */
class demoTask extends Task{
	public function run(){
	    $db=Db::setConfig(Config::get('DB'));
	    $res=$db->table("表名")->find();
	    var_dump($res);
	}
}

```
### 查询多条
``` php
<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\Config;
use core\lib\Db;
/**
 * 测试任务
 */
class demoTask extends Task{
	public function run(){
	    $db=Db::setConfig(Config::get('DB'));
	    $res=$db->table("表名")->where("id=1")->select();
	    var_dump($res);
	}
}

```
### 查询总数
``` php

```
### where条件
``` php
<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\Config;
use core\lib\Db;
/**
 * 测试任务
 */
class demoTask extends Task{
	public function run(){
	    $db=Db::setConfig(Config::get('DB'));
	    $res=$db->table("表名")->where("id=1")->find();
	    //或者
	    $res=$db->table("表名")->where(array("id"=>1))->find();
	    var_dump($res);
	}
}

```
### in
``` php

```
### group by
``` php
<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\Config;
use core\lib\Db;
/**
 * 测试任务
 */
class demoTask extends Task{
	public function run(){
	    $db=Db::setConfig(Config::get('DB'));
        $db->table("表名")->where(array("room_id"=>1))->group("status")->select();
	}
}

```
### left join
``` php

```
### 执行底层sql操作
``` php
<?php
namespace tasks\demo;
use core\lib\Task;
use core\lib\Config;
use core\lib\Db;
/**
 * 测试任务
 */
class demoTask extends Task{
	public function run(){
	    $db=Db::setConfig(Config::get('DB'));
        $res=$db->table("表名")->model()->select("id")->from("表名")->row();
        var_dump($res);
	}
}

```