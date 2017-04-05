<?php
/**
 * taskPHP
 * @author     码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 * @copyright  taskPHP
 * @license    https://git.oschina.net/cqcqphper/TimePhp
 */
namespace core\lib;

/**
 * 类定位器
 *
 * defined(VENDOR_ROOT) define(VENDOR_ROOT, __DIR__ . '/pram3/vendor');
 * require_once __DIR__ . '/pram3/src/Pram/Locator.php';
 * $locator = \Pram\Locator::getInstance();
 * $locator->addNamespace('NotORM', VENDOR_ROOT . '/NotORM');
 * //OR
 * $locator->addClass(VENDOR_ROOT . '/NotORM/NotORM.php',
 *         'NotORM', 'NotORM_Result', 'NotORM_Row', 'NotORM_Literal', 'NotORM_Structure');
 * //Complex
 * $locator->addNamespace('React', array(
 *     '.' => VENDOR_ROOT . '/React/src',
 *     'Promise' => VENDOR_ROOT . '/Promise/src/React',
 * ));
 * spl_autoload_register(array($locator, 'autoload'));
 */
final class Locator
{
    private static $instance = null;
    private $have_branches = false; //是否允许子命名空间分散在不同分支
    private $classes = array();  //已注册的class/interface/trait对应的文件
    private $namespaces = array(); //已注册的namespace对用的起始目录

    private function __construct(){
        
    }

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
     * 检查指定class/interface/trait是否已存在
     * @param string $class 要检查的完整class/interface/trait名称
     * @param bool $autoload 如果当前不存在，是否尝试PHP的自动加载功能
     * @return bool
     */
    public static function exists($class, $autoload = true){
        return class_exists($class, $autoload)
                || interface_exists($class, $autoload)
                || trait_exists($class, $autoload);
    }

    /**
     * 自动加载方法，用于spl_autoload_register注册
     * @param string $class 要寻找的完整class/interface/trait名称
     * @return bool
     */
    public function autoload($class){
        $class = trim($class, '\\_');
        if (isset($this->classes[$class])) { //在已知类中查找
            require_once $this->classes[$class];
            return self::exists($class, false);
        }
        $ns_check = $this->checkNamespace($class); //在已知域名中查找
        return $ns_check === true ? true : false;
    }

    /**
     * 将对象的autoload方法注册到PHP系统
     * 在这之后往对象中添加的class和namespace也起作用
     * @return bool
     */
    public function register(){
        return spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * 当自动加载class,class2,class3,...时，将filename文件包含进来
     * @param string $filename 这些class/interface/trait所在的文件或入口文件
     * @param string $class 完整class/interface/trait名称
     * @param ... 其他class/interface/trait名称
     * @return this
     */
    public function addClass($filename, $class){
        $classes = func_get_args();
        $filename = array_shift($classes);
        if (is_readable($filename)) {
            foreach ($classes as $class) {
                $this->classes[trim($class, '\\')] = $filename;
            }
        }
        ksort($this->classes);
        return $this;
    }

    /**
     * 当自动加载的namespace/class以某个词ns开头时，尝试在dir目录寻找匹配文件
     * @param string $ns namespace前缀
     * @param string/array $dir namespace所在顶层目录
     * @return this
     */
    public function addNamespace($ns, $dir){
        if (is_array($dir)) {
            $this->have_branches = true;
        }
        $this->namespaces[trim($ns, '\\')] = $dir;
        ksort($this->namespaces);
        return $this;
    }

    /**
     * Namespace/class自动加载时，寻找匹配文件的方式
     * @param string $class 要寻找的完整class/interface/trait名称
     * @return bool
     */
    public function checknamespace($class){
        $tok = strtok($class, '\\_');
        $length = strlen($tok) + 1;
        if (isset($this->namespaces[$tok])) {
            $path = $this->namespaces[$tok];
            //找到子命名空间所在分支
            if ($this->have_branches) {
                $length = self::dispatchBranch($path, $tok, $length);
            }
            //先试试一步到位，用于符合PSR-0标准的库
            //$fname = $path . '/' . $tok . '/';
            $fname = $path . DS;
            $fname .= str_replace(array('\\', '_'), DS, substr($class, $length));
            if (file_exists($fname . '.php')) {
                require_once $fname . '.php';
                if (self::exists($class, false)) {
                    return true;
                }
            }
            //尝试循序渐进地检查
            return self::seekStepByStep($class, $path, $tok);
        }else{
            /* $class_=substr($class, $length);
            if($class_==''){
                die("Class '".$class."' not found.");
            } */
            $class_=str_replace(array('\\', '_'), DS, $class);
            $fname=APP_ROOT.DS.$class_;
            require_once $fname . EXT;
            if (self::exists($class, false)) {
                return true;
            }
        }
    }

    /**
     * 找到子命名空间所在分支
     * @param string $path 已经探索的路径
     * @param string $tok 路径中的一段
     * @param int $length 已探索路径长度
     * @return int $length 已探索路径长度
     */
    private static function dispatchBranch(&$path, &$tok, $length)
    {
        while (is_array($path)) {
            $prev_tok = $tok;
            $tok = strtok('\\_');
            $length += strlen($tok) + 1;
            if (isset($path[$tok])) {
                $path = $path[$tok];
            } else {
                $path = $path['.'] . '/' . $prev_tok;
                break;
            }
        }
        return $length;
    }

    /**
     * 循序渐进地检查目标对应的路径
     * @param string $class 要寻找的完整class/interface/trait名称
     * @param string $path 已经探索的路径
     * @param string $tok 路径中的一段
     * @return bool/null class/interface/trait是否存在
     */
    private static function seekStepByStep($class, $path, $tok)
    {
        while ($tok) {
            $path .= '/' . $tok;
            //先检查文件，再检查目录，次序不可颠倒
            if (file_exists($path . '.php')) { //找到文件了
                require_once $path . '.php';
                if (self::exists($class, false)) {
                    return true;
                }
            }
            if (!file_exists($path)) { //目录不对，不要再找了
                return false;
            }
            $tok = strtok('\\_');
        }
    }
}