## windows下安装php多线程扩展pthreads教程
本教程主要是为taskPHP框架编写，实用于windows系统php-cli运行模式 	 
扩展地址：http://docs.php.net/manual/zh/book.pthreads.php

## 注意事项
编写phpinfo.php
``` php
<?php
phpinfo();
``` 
php-cli运行phpinfo.php。<br>
php5.3或以上，且为线程安全版本。<br>
通过phpinfo()查看Thread Safety为enabled则为线程安全版。<br>
通过phpinfo()查看Compiler项可以知道使用的编译器。<br>

本人的php版本为:PHP Version 5.5.12<br>
Compiler:MSVC11 (Visual C++ 2012)<br>
Architecture:x86<br>

## 安装流程
一、下载pthreads扩展
下载地址：http://windows.php.net/downloads/pecl/releases/pthreads<br>
根据本人环境，我下载的是php_pthreads-2.0.10-5.5-ts-vc11-x86.zip。<br>
2.0.10代表pthreads的版本。<br>
5.5代表php的版本。<br>
ts表示php要线程安全版本的。<br>
vc11表示php要Visual C++ 2012编译器编译的。<br>
x86则表示32位。<br>

二、安装pthreads扩展
复制php_pthreads.dll 到目录 bin\php\ext\ 下面,(本人路径D:\wamp\bin\php\php5.5.12\ext)。<br>
复制pthreadVC2.dll 到目录 bin\php\ 下面,(本人路径D:\wamp\bin\php\php5.5.12)。<br>
复制pthreadVC2.dll 到目录 C:\windows\system32 下面。<br>
打开php配置文件php.ini(注意php.ini文件路径需要从以上phpinfo中得到),在后面加上extension=php_pthreads.dll<br>
提示:Windows系统需要将 pthreadVC2.dll 所在路径加入到 PATH 环境变量中。我的电脑--->鼠标右键--->属性--->高级--->环境变量--->系统变量--->找到名称为Path的--->编辑--->在变量值最后面加上pthreadVC2.dll的完整路径(本人的为C:\WINDOWS\system32\pthreadVC2.dll)。

三、测试pthreads扩展
编写demo.php
``` php
<?php
header("Content-type: text/html; charset=utf-8"); 
class demo extends Thread {
    public $url;
    public $result;
    public function __construct($url) {
        $this->url = $url;
    }
    
    public function run() {
        if ($this->url) {
            $this->result = http_curl_get($this->url);
        }
    }
}
function http_curl_get($url) {
    $curl = curl_init();  
    curl_setopt($curl, CURLOPT_URL, $url);  
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);  
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.2)');  
    $result = curl_exec($curl);  
    curl_close($curl);  
    return $result;  
}
for ($i = 0; $i < 10; $i++) {
    $urls[] = 'http://www.baidu.com/s?wd='. rand(10000, 20000);
}
/* 多线程速度测试 */
$t = microtime(true);
foreach ($urls as $key=>$url) {
    $workers[$key] = new demo($url);
    $workers[$key]->start();
}
foreach ($workers as $key=>$worker) {
    while($workers[$key]->isRunning()) {
        usleep(100);  
    }
    if ($workers[$key]->join()) {
        $workers[$key]->result;
    }
}
$e = microtime(true);
echo "多线程耗时：".($e-$t)."秒<br>";  
/* 单线程速度测试 */
$t = microtime(true);
foreach ($urls as $key=>$url) {
    http_curl_get($url);
}
$e = microtime(true);
echo "For循环耗时：".($e-$t)."秒<br>";  
``` 
测试结果如下：<br>
多线程耗时：2.8371710777282714844秒<br>
For循环耗时：10.941586017608642578秒