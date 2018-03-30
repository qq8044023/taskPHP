<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://gitee.com/cqcqphper/taskPHP
 */
namespace taskphp;

/**
 * 自动加载类
 * 为了配合使用composer所以升级支持psr-4
 * @author cqcqphper 小草<cqcqphper@163.com>
 */
final class Locator{
    /**
     * 实例
     * @var Locator
     */
    private static $instance = null;
    
    /**
     * 维护一个命名空间前缀和具体路径对应的映射表
     * 一个命名空间前缀中可以有多个路径
     * @var array
     */
    protected $prefixes = array();
    
    /**
    * Locator单例
    * @return instance of Locator
    */
    public static function getInstance(){
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$instance->register();
        }
        return self::$instance;
    }
    
    /**
     * 注册加载函数到自动加载函数栈中
     * 
     * @return void
     */
    public function register(){
        // 注册系统自动加载
        spl_autoload_register(array($this, 'loadClass'),true,true);
        // 注册命名空间定义
        $this->addNamespace('taskphp', TASKPHP_PATH.DS.'lib'.DS);
    }
    /**
     * 给一个命名空间前缀中添加具体的路径.
     *
     * @param string $prefix 命名空间前缀
     * @param string $base_dir 要添加到命名空间中的路径
     * @param bool $prepend 如果为true，则将该路径添加到命名空间对应数组的
     * 最前面，而不是添加到末尾；这个会影响自动加载的搜索文件
     * 
     * @return void
     */
    public function addNamespace($prefix, $base_dir, $prepend = false){
        // 正规化命名空间前缀
        $prefix = trim($prefix, '\\') . '\\';
        // 正规化命名空间对应的目录
        $base_dir = rtrim($base_dir, DS) . '/';
        // 初始化命名空间中该前缀的数组
        if (isset($this->prefixes[$prefix]) === false) {
            $this->prefixes[$prefix] = array();
        }
        // 将目录添加到命名空间数组中$prefix前缀数组中
        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $base_dir);
        } else {
            array_push($this->prefixes[$prefix], $base_dir);
        }
    }
    /**
     * 加载给定类的对应的类库文件
     *
     * @param string $class 完整的类库名称.
     * @return mixed 成功时返回类名对应的类库文件路径，失败时返回false.
     */
    public function loadClass($class){
        // 当前的命名空间前缀
        $prefix = $class;
        //通过命名空间去查找对应的文件
        while (false !== ($pos = strrpos($prefix, '\\'))) {
            // 可能存在的命名空间前缀
            $prefix = substr($class, 0, $pos + 1);
            // 剩余部分是可能存在的类
            $relative_class = substr($class, $pos + 1);
            //试图加载prefix前缀和relitive class对应的文件
            $mapped_file = $this->loadMappedFile($prefix, $relative_class);
            if ($mapped_file) {
                return $mapped_file;
            }
            // 移动命名空间和relative class分割位置到下一个位置
            $prefix = rtrim($prefix, '\\');   
        }
        // 未找到试图加载的文件
        return false;
    }
    
    /**
     * 加载命名空间前缀和relative class映射的文件.
     * @param string $prefix 命名空间前缀.
     * @param string $relative_class relative class名称.
     * @return mixed 成功返回映射的文件路径，失败返回false.
     */
    protected function loadMappedFile($prefix, $relative_class){
        // 命名空间前缀数组中不存在prefix命名空间前缀，返回false.
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }
            
        // 查看此命名空间前缀的基目录
        // 遍历命名空间前缀对应的目录数组，知道找到映射的文件
        foreach ($this->prefixes[$prefix] as $base_dir) {
            // 用具体路径替换掉命名空间前缀,
            // 替换relative class中的命名空间分隔符为目录分隔符
            // 添加.php后缀
            $file = $base_dir
                  . str_replace('\\', DS, $relative_class)
                  . '.php';
            // 如果映射文件存在加载对应的文件
            if ($this->requireFile($file)) {
         		// 返回成功加载的文件路径
                return $file;
            }
        }
        // 未找到要映射的文件返回false
        return false;
    }
    
    /**
     * 如果文件存在，从文件系统中加载他到运行环境中.
     * @param string $file 要加在的文件.
     * @return bool 文件存在返回true，否在返回false.
     */
    protected function requireFile($file){
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }
}