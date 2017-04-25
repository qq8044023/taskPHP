## Mysql数据库操作

## 详细使用说明
```php
use core\lib\Utils;
```
1. 配置mysql 将以下配置代码加入到你的xxTask.php同级目录的config.php里面
``` php
	/**
     * 数据库配置
     **/
    'DB'=>array(
        'db_type'       =>'MYSQL',//数据库类型
        'db_host'       =>'127.0.0.1',//地址
        'db_username'   =>'root',//账户
        'db_password'   =>'',//密码
        'db_prot'       =>'3306',//端口
        'db_name'       =>'dbname'//选中的数据库
    ),
```

2. 添加数据
``` php
	$db=Utils::db(Utils::config('DB','demo'));
    $data=array(
        "player_id"=>1,
        "item_id"=>2,
        "rows"=>3
    );
    $db->table("表名")->add($data);
```

3. 删除数据
``` php
	$db=Utils::db(Utils::config('DB','demo'));
	$res=$db->table("表名")->where("id=1")->delete();
```

4. 更新数据
``` php
	$db=Utils::db(Utils::config('DB','demo'));
    $db->table("表名")->where(array("room_id"=>1))->save(array("status"=>1));
```

5. 查询单条
``` php
	$db=Utils::db(Utils::config('DB','demo'));
	$res=$db->table("表名")->find();
	var_dump($res);
```

6. 查询多条
``` php
	$db=Utils::db(Utils::config('DB','demo'));
	$res=$db->table("表名")->where("id=1")->select();
	var_dump($res);
```

7. 查询总数
``` php
//==
```

8. where条件
``` php
	$db=Utils::db(Utils::config('DB','demo'));
	$res=$db->table("表名")->where("id=1")->find();
	//或者
	$res=$db->table("表名")->where(array("id"=>1))->find();
	var_dump($res);
```
9. in
``` php
//==
```

10. group by
``` php
	$db=Utils::db(Utils::config('DB','demo'));
	$db->table("表名")->where(array("room_id"=>1))->group("status")->select();
```

11. left join
``` php
//==
```

12. 执行底层sql操作
``` php
	$db=Utils::db(Utils::config('DB','demo'));
	$res=$db->table("表名")->model()->select("id")->from("表名")->row();
	var_dump($res);
```
