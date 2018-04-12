## Mysql数据库操作

## 详细使用说明
```php
use taskphp\Utils;
```
1. 配置mysql config.php里面

``` php

    /**
     * 数据库配置
     *   */
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


```

2.数据库操作

``` php

		//数据库操作 获取一条数据
	    /*  $res=Utils::db('table1')->find();
	    Utils::log($res);*/
	     
	    //方法二
	     $res=Utils::db()->table("table1")->where("id=1")->limit(2)->order("id DESC")->select();
	     Utils::log($res);
	     
	    /* //方法三
	     $res=Utils::db()->table("user")->alias("a")->join("user_third AS b ON a.uid=b.uid","LEFT")->where("a.status=1")->limit(2)->order("a.uid DESC")->select();
	     Utils::log($res); */
	     
	    //Utils::db()->table("user")->getSql()  打印sql语句
	     
	    /* //方法四
	     $res=Utils::db()->table("user_phone_log")->where(array("phone_log_id"=>2))->update(array("uid"=>3));
	     Utils::log($res); */
	     
	    //方法五
	    /* 
	     $res=Utils::db()->table("user_phone_log")->add(array(
	     "uid"         =>22,
	     "status"      =>1,
	     "create_date" =>time(),
	     "phone"       =>13111111
	     ));
	     Utils::log($res); */
	     
	    /* //方法六
	     $res=Utils::db()->table("user_phone_log")->where(array("uid"=>22))->delete();
	     Utils::log($res); */

```



