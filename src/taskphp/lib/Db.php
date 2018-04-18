<?php
// +----------------------------------------------------------------------
// | 文件描述
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 
// | Data  2018年3月20日 上午10:55:58 
// | Version  1.0.0
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Author: 村长 <8044023@qq.com> 
// +----------------------------------------------------------------------
namespace taskphp;
class Db extends Sql{
    protected static $DB;
    function __construct($table=null){
        if(!extension_loaded('pdo')){
            Console::log('ERROR:pdo module has not been opened');die;
        }
        if(!Utils::global_var('pdo_object')) Utils::global_var('pdo_object',self::InitPDO());
        self::$DB = Utils::global_var('pdo_object');
        if($table) $this->table($table);
    }

    /**
     * 返回PDO对象
     * @return [type]        [description]
     */
    static function db(){
        if(!Utils::global_var('pdo_object')) Utils::global_var('pdo_object',self::InitPDO());
        return Utils::global_var('pdo_object');
    }

    /**
     * 初始化PDO连接
     */
    private static function InitPDO(){
        Utils::statistics('initpdo_begin');
        if(empty(parent::$CONFIG)) parent::$CONFIG  = Utils::config('db');
        if(empty(parent::$CONFIG['port'])) parent::$CONFIG['port'] = 3306;
        $dsn = 'mysql:host='.parent::$CONFIG['host'].';dbname='.parent::$CONFIG['name'].';port='.parent::$CONFIG['port'];
        $config = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES '.parent::$CONFIG['charset'],
            \PDO::ATTR_EMULATE_PREPARES=>false,//是否模拟预处理
            \PDO::ATTR_STRINGIFY_FETCHES=>false,//是否将数值转换为字符串
            //	\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY=>true,//缓冲查询
        );
        $DB = new \PDO($dsn,parent::$CONFIG['username'],parent::$CONFIG['password'],$config);
        $DB->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Utils::statistics('initpdo_end');
        Utils::log('taskphp\Db [--END--][RunTime:'.Utils::statistics('initpdo_begin','initpdo_end',6).'s]');
        return $DB;
    }

    /**
     * 重新建立MySql连接
     */
    private static function reconnect(){
        self::$DB = null;
        self::$DB = self::InitPDO();
    }

    /**
     * 执行PDO操作
     */
    function fetchResult($arg=true,$type=1,$fetch=null){
        try{
            $pre = self::$DB->prepare($this->sql);
            if($arg) $pre->execute($this->bind_value);
            else $pre->execute();
            if(!$pre){
                self::reconnect();
                $this->fetchResult($arg,$type,$fetch);
            }
            switch($type){
                case 0:
                    return $pre;
                    break;
                case 1:
                    return $pre->fetch($fetch);
                    break;
                case 2:
                    return $pre->fetchAll($fetch);
                    break;
                case 3:
                    return $pre->rowCount();
                    break;
            }
        }catch(\PDOException $e){
            if($e->errorInfo[1] == 2006 || $e->errorInfo[1] == 2013){
                self::reconnect();
                $this->fetchResult($arg,$type,$fetch);
            }else{
                $this->throwErr($e);
            }
        }
    }

    /**
     * 抛出异常
     */
    private function throwErr($e){
        $msg = $e->getMessage();
        $err_msg = "SQL:".$this->sql." ".$msg;
        throw new \PDOException($err_msg, (int)$e->getCode());
    }

    /**
     * 字段数据求和
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    public function sum($field){
        $_field = self::set_field($field);
        $this->field = "SUM({$_field}) AS {$field}_sum";
        $sum = $this->find();
        return $sum["{$field}_sum"];
    }

    /**
     * 返回最大值
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    public function max($field){
        $_field = self::set_field($field);
        $this->field = "MAX({$_field}) AS {$field}_max";
        $max = $this->find();
        return $max[$field . '_max'];
    }

    /**
     * 返回最小值
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    public function min($field){
        $_field = self::set_field($field);
        $this->field = "MIN({$_field}) AS {$field}_min";
        $min = $this->find();
        return $min["{$field}_min"];
    }

    /**
     * 返回平均值
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    public function avg($field){
        $_field = self::set_field($field);
        $this->field = "AVG({$_field}) AS {$field}_avg";
        $avg = $this->find();
        return $avg["{$field}_avg"];
    }

    /**
     * 单独更新某字段的值
     * @param [type] $field [description]
     * @param [type] $value [description]
     */
    public function setField($field,$value){
        return $this->update(array($field=>$value));
    }

    public function subQuery($field=null){
        if($field) $this->field = self::set_field($field);
        $sql = $this->SQL();
        $this->order = null;
        $this->where = null;
        $this->logic = null;
        $this->join = null;
        $this->deleteTable = null;
        $this->tables = null;
        $this->field = '*';
        $this->having = null;
        $this->group = null;
        $this->limit = null;
        return $sql;
    }

    /**
     * 多条数据查找
     * @param  [type] $field [字段名，返回field字段的数据（一维数组）]
     * @return [type]        [description]
     */
    public function select($field=null,$lock=false){
        Utils::statistics('sqlend_begin');
        if($field){
            $this->field = self::set_field($field);
            $this->sql = $this->SQL();
            if($lock) $this->sql . ' FOR UPDATE';
            $result = $this->fetchResult(true,2,\PDO::FETCH_COLUMN);
        }else{
            $this->sql = $this->SQL();
            if($lock) $this->sql . ' FOR UPDATE';
            $result = $this->fetchResult(true,2,\PDO::FETCH_ASSOC);
        }
        Utils::statistics('sqlend_end');
        self::sqlend();
        $this->cl();
        return $result;
    }

    /**
     * 返回符合条件的记录条数
     * @return [type] [description]
     */
    public function count(){
        Utils::statistics('sqlend_begin');
        $this->sql = $this->SQL();
        $num = $this->fetchResult(true,3);
        Utils::statistics('sqlend_end');
        self::sqlend();
        $this->cl();
        return $num;
    }

    /**
     * 获取js分页数据
     * @param  integer $num   [每页数据量]
     * @param  integer $total [是否返回总数据量]
     * @param  integer $page  [当前页码]
     * @param  integer $max   [限制最大总分页数]
     * @return [type]         [description]
     */
    public function jsPage($num=10,$total=0,$page=0,$max=0){
        $this->page = null;
        if(!$page) $page = empty($_GET['p']) ? 1 : $_GET['p'];
        if($total){
            if($max) $this->limit($max * $num);
            $this->page['nowpage'] = $page;
            $this->sql = $this->SQL();
            $this->page['total'] = $this->fetchResult(true,3);
        }
        $start = ($page - 1) * $num;
        return $this->limit($start,$num);
    }

    /**
     * 数据分页
     * @param  [type]  $num      [每页数据量]
     * @param  integer $pageRoll [返回的最大的分页数量]
     * @param  boolean $page     [当前页码]
     * @return [type]            [description]
     */
    public function page($num,$pageRoll=10,$page=false){
        if(!$page) $page = empty($_GET['p']) ? 1 : $_GET['p'];
        $start = ($page - 1) * $num;
        $stop = $num;
        $this->page = null;
        Utils::statistics('page_begin');
        $this->sql = $this->SQL();
        $this->page['total'] = $this->fetchResult(true,3);
        Utils::statistics('page_end');
        Utils::log(get_class($this).' [--END--][RunTime:'.Utils::statistics('page_begin','page_end',6).'s]');
        $this->page['li'] = [];
        $this->page['pages_num'] = !empty($this->page['total']) ? (INT)ceil($this->page['total'] / $num) : 1;
        $args = $_GET;
        if($this->page['pages_num'] > 1){
            $Pnow = intval($pageRoll / 2);
            if($page > $Pnow && $this->page['pages_num'] > $pageRoll){
                $i = $page - $Pnow;
                $Pend = $i + $pageRoll - 1;
                if($Pend > $this->page['pages_num']){
                    $Pend = $this->page['pages_num'];
                    $i = $Pend - $pageRoll + 1;
                }
            }else{
                $i = 1;
                $Pend = $pageRoll > $this->page['pages_num'] ? $this->page['pages_num'] : $pageRoll;
            }
            for($i;$i <= $Pend;$i++){
                $args['p'] = $i;
                $this->page['li'][$i] = $page == $i ? 'javascript:;' : U(CONTROLLER_NAME . '/' . ACTION_NAME,$args);
            }
        }
        $this->page['nowpage'] = $page;
        if($page > 1){
            $args['p'] = $page - 1;
            $this->page['prev'] = U(CONTROLLER_NAME . '/' . ACTION_NAME,$args);
        }else{
            $this->page['prev'] = 'javascript:;';
        }
        if($page < $this->page['pages_num']){
            $args['p'] = $page + 1;
            $this->page['next'] = U(CONTROLLER_NAME . '/' . ACTION_NAME,$args);
        }else{
            $this->page['next'] = 'javascript:;';
        }
        $args['p'] = 1;
        $this->page['start'] = $page > 1 ? U(CONTROLLER_NAME . '/' . ACTION_NAME,$args) : 'javascript:;';;
        $args['p'] = $this->page['pages_num'];
        $this->page['end'] = $page == $this->page['pages_num'] ? 'javascript:;' : U(CONTROLLER_NAME . '/' . ACTION_NAME,$args);
        return $this->limit($start,$stop);
    }

    /**
     * 受影响的行数
     * @return [type] [description]
     */
    private function returnNum(){
        Utils::statistics('sqlend_begin');
        $num = $this->fetchResult(true,3);
        $this->cl();
        Utils::statistics('sqlend_end');
        self::sqlend();
        return $num;
    }

    /**
     * 更新数据（不执行自动验证）
     * @param  [type]  $add  [description]
     * @param  boolean $safe [description]
     * @return [type]        [description]
     */
    public function update($add,$safe=true){
        $update_sql = $this->add_update($add,$safe);
        $sql = implode(',',$update_sql);
        $where = $this->sql_where();
        $join = $this->join ? ' ' . implode(' ',$this->join) : '';
        $this->sql = "UPDATE {$this->table}{$join} SET {$sql}{$where}";
        return $this->returnNum();
    }

    /**
     * 更新数据（执行自动验证）
     * @param  [type]  $add  [description]
     * @param  boolean $safe [description]
     * @return [type]        [description]
     */
    public function save($add,$safe=true){
        if(method_exists($this,'create') && !$this->create($add,'update')) return false;
        return $this->update($add,$safe);
    }

    /**
     * 字段值自增
     * @param [type]  $field [description]
     * @param integer $num   [description]
     */
    public function setInc($field,$num=1){
        $_field = self::set_field($field);
        $where = $this->sql_where();
        $join = $this->join ? ' ' . implode(' ',$this->join) : '';
        $this->sql = "UPDATE {$this->table}{$join} SET {$_field}={$_field} + {$num}{$where}";
        return $this->returnNum();
    }

    /**
     * 字段值自减
     * @param [type]  $field [description]
     * @param integer $num   [description]
     */
    public function setDec($field,$num=1){
        $_field = self::set_field($field);
        $where = $this->sql_where();
        $join = $this->join ? ' ' . implode(' ',$this->join) : '';
        $this->sql = "UPDATE {$this->table}{$join} SET {$_field}={$_field} - {$num}{$where}";
        return $this->returnNum();
    }

    /**
     * 字段值乘以
     * @param [type]  $field [description]
     * @param integer $num   [description]
     */
    public function setMul($field,$num=2){
        $_field = self::set_field($field);
        $where = $this->sql_where();
        $join = $this->join ? ' ' . implode(' ',$this->join) : '';
        $this->sql = "UPDATE {$this->table}{$join} SET {$_field}={$_field} * {$num}{$where}";
        return $this->returnNum();
    }

    /**
     * 字段值除以
     * @param [type]  $field [description]
     * @param integer $num   [description]
     */
    public function setDiv($field,$num=2){
        $_field = self::set_field($field);
        $where = $this->sql_where();
        $join = $this->join ? ' ' . implode(' ',$this->join) : '';
        $this->sql = "UPDATE {$this->table}{$join} SET {$_field}={$_field} / {$num}{$where}";
        return $this->returnNum();
    }

    /**
     * 数据删除
     * @param  string $table [要执行删除操作的表别名，没有where条件时，需指定此参数为ALL来删除全部数据]
     * @return [type]        [受影响行数]
     */
    public function delete($table=''){
        $deleteAll = false;
        if(is_array($table)){
            if(!empty($table['all']) || !empty($table['ALL'])) $deleteAll = true;
            if($table[0]) $this->deleteTable = $table[0];
        }else{
            if($table == 'all' || $table == 'ALL')	$deleteAll = true;
            else $this->deleteTable = $table;
        }
        $where = $this->sql_where();
        if(!$where && !$deleteAll){
            throw new \PDOException("要删除[{$this->table}]表的所有记录？请使用delete('all')");
        }
        $join = $this->join ? ' ' . implode(' ',$this->join) : '';
        $this->sql = "DELETE {$this->deleteTable} FROM {$this->table}{$join}{$where}";
        return $this->returnNum();
    }

    /**
     * 插入数据（不执行自动验证）
     * @param  [array]  $add  [字段名=>值]
     * @param  boolean  $safe [安全模式：是否转译特殊字符]
     * @return [type]         [新插入数据的主键值，没有主键返回true]
     */
    public function insert($add,$safe=true){
        $add_sql = $this->add_update($add,$safe);
        $sql = implode(',',$add_sql);
        $this->sql = "INSERT INTO {$this->table} SET {$sql}";
        Utils::statistics('sqlend_begin');
        $this->fetchResult(true,0);
        $id = self::$DB->lastInsertId();
        $this->cl();
        Utils::statistics('sqlend_end');
        self::sqlend();
        return $id ?: true;
    }

    /**
     * 有则更新，无则插入（该操作不执行自动验证，请自行检查数据合法性）
     * @param  [type]  $add    [执行插入操作的数据]
     * @param  [type]  $update [执行更新操作的数据]
     * @param  boolean $safe   [安全模式：是否转译特殊字符]
     * @return [type]          [插入数据时返回主键值，更新数据时返回-1，无影响时返回0]
     */
    public function ifInsert($add,$update=null,$safe=true){
        $add_sql = $this->add_update($add,$safe);
        $add_sql = implode(',',$add_sql);
        $update_sql = $this->add_update($update ?: $add,$safe);
        $update_sql = implode(',',$update_sql);
        $this->sql = "INSERT INTO {$this->table} SET {$add_sql} ON DUPLICATE KEY UPDATE {$update_sql}";
        Utils::statistics('sqlend_begin');
        $num = $this->fetchResult(true,3);
        $this->cl();
        Utils::statistics('sqlend_end');
        self::sqlend();
        if(!$num) return 0;
        return 1 == $num ? (self::$DB->lastInsertId() ?: true) : -1;
    }

    /**
     * 插入数据（执行自动验证）
     * @param [type]  $add  [字段名=>值]
     * @param boolean $safe [安全模式：是否转译特殊字符]
     * @return [type]       [新插入数据的主键值，没有主键返回true]
     */
    public function add($add,$safe=true){
        if(method_exists($this,'create') && !$this->create($add,'add')) return false;
        return $this->insert($add,$safe);
    }

    /**
     * 单条数据查找
     * @param  [type]  $field [字段名：返回此字段的值，字符串]
     * @return [array]        [一维数组]
     */
    public function find($field=null,$lock=false){
        Utils::statistics('sqlend_begin');
        if($field){
            $this->field = self::set_field($field);
            $this->sql = $this->SQL();
            if($lock) $this->sql . ' FOR UPDATE';
            $result = $this->fetchResult(true,1,\PDO::FETCH_COLUMN);
        }else{
            $this->sql = $this->SQL();
            if($lock) $this->sql . ' FOR UPDATE';
            $result = $this->fetchResult(true,1,\PDO::FETCH_ASSOC);
        }
        Utils::statistics('sqlend_end');
        self::sqlend();
        $this->cl();
        return $result;
    }

    /**
     * 返回数据库的所有表名
     * @return [type] [description]
     */
    public function getTables(){
        $this->sql = "show tables";
        Utils::statistics('sqlend_begin');
        $result = $this->fetchResult(false,2,\PDO::FETCH_COLUMN);
        Utils::statistics('sqlend_end');
        self::sqlend();
        return $result;
    }

    /**
     * 返回表的所有字段名
     * @return [type] [description]
     */
    public function getFields(){
        if($this->fields) return $this->fields;
        $this->sql = 'DESC '.parent::$CONFIG['prefix'].$this->subTableName;
        Utils::statistics('sqlend_begin');
        $list = $this->fetchResult(false,2,\PDO::FETCH_ASSOC);
        Utils::statistics('sqlend_end');
        self::sqlend();
        if(!$list) return null;
        foreach($list as &$v){
            $this->fields[] = $v['Field'];
            if('PRI' == $v['Key']) $this->primaryKey[] = $v['Field'];
        }
        return $this->fields;
    }

    public function getPrimaryKey(){
        if($this->fields) $this->getFields();
        return $this->primaryKey;
    }
    /**
     * 开始事务处理
     * @return [type] [description]
     */
    function begin(){
        try{
            return self::$DB->beginTransaction();
        }catch(\PDOException $e){
            if($e->errorInfo[1] == 2006 || $e->errorInfo[1] == 2013){
                self::reconnect();
                $this->begin();
            }else{
                $this->throwErr($e);
            }
        }
    }
    /**
     * 提交事务
     * @return [type] [description]
     */
    function commit(){
        return self::$DB->commit();
    }
    /**
     * 回滚
     * @return [type] [description]
     */
    function rollback(){
        return self::$DB->rollback();
    }

    /**
     * 执行sql语句（多记录查找）
     * @param  [type] $sql [description]
     * @return [type]      [description]
     */
    function queryAll($sql){
        $this->sql = $sql;
        return $this->fetchResult(false,2,\PDO::FETCH_ASSOC);
    }

    /**
     * 执行sql语句（单条记录查找）
     * @param  [type] $sql [description]
     * @return [type]      [description]
     */
    function queryOne($sql){
        $this->sql = $sql;
        return $this->fetchResult(false,1,\PDO::FETCH_ASSOC);
    }

    /**
     * 执行sql语句（写入操作）
     * @param  [type] $sql [description]
     * @return [type]      [description]
     */
    function submit($sql){
        $this->sql = $sql;
        Utils::statistics('sqlend_begin');
        $num = $this->fetchResult(false,3);
        Utils::statistics('sqlend_end');
        self::sqlend();
        if(stristr($sql,'insert')) return self::$DB->lastInsertId() ?: true;
        else return $num;
    }

    /**
     * 统计debug信息
     * @param  [type] $sql  [description]
     * @return [type]       [description]
     */
    private function sqlend(){
        if(Utils::config('log.debug')){
            Utils::log('SQL:'.$this->sql.'[bind_value:'.json_encode($this->bind_value).']'.'[RunTime:'.Utils::statistics('sqlend_begin','sqlend_end',6).'s]');
        }
    }
    function __call($functionName,$args){
        $args = is_array($args) ? implode(',',$args) : $args;
        try{
            return self::$DB->$functionName($args);
        }catch(\PDOException $e){
            if($e->errorInfo[1] == 2006 || $e->errorInfo[1] == 2013){
                self::reconnect();
                return self::$DB->$functionName($args);
            }else{
                $this->throwErr($e);
            }
        }
    }
}