<?php
// +----------------------------------------------------------------------
// | 文件描述
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 
// | Data  2018年3月20日 上午10:56:21 
// | Version  1.0.0
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Author: 村长 <8044023@qq.com> 
// +----------------------------------------------------------------------
namespace taskphp;
abstract class Sql{
    static protected $CONFIG;
    static protected $dberror;//错误信息
    protected $table;
    protected $tables;
    protected $primaryKey;
    protected $deleteTable;
    protected $subTableName;
    protected $logic;
    protected $where;//WHERE条件数组
    protected $bind_value;//绑定的参数数组
    protected $fields;
    protected $field='*';
    protected $join;
    protected $limit;
    protected $order;
    protected $group;
    protected $having;
    protected $page;
    protected $r=0;
    protected $sql;
    protected $args;
    /**
     * 获取表前缀
     */
    public function getPrefix(){
        return self::$CONFIG['prefix'];
    }

    /**
     * 获取错误信息
     * @param  boolean $arr [是否返回数组]
     * @return [type]       [description]
     */
    public function getError($arr=false){
        if(empty(self::$dberror)) return NULL;
        if($arr || is_string(self::$dberror)) return self::$dberror;
        else{
            return implode("<br>",self::$dberror);
        }
    }

    /**
     * 获取最后执行的sql语句
     * @return [string] [description]
     */
    public function getSql(){
        return $this->sql;
    }

    /**
     * 获取绑定的参数
     * @return [array] [description]
     */
    public function getArgs(){
        return $this->args;
    }

    /**
     * 获取分页数据
     * @return [array] [description]
     */
    public function getPage(){
        return $this->page;
    }

    /**
     * 设置要操作的字段
     * @param  [string] $field [description]
     * @return [type]        [description]
     */
    public function field($field){
        if(!$field) return $this;
        if(!empty($field['EXCEPT'])){
            $except = is_array($field['EXCEPT']) ? $field['EXCEPT'] : explode(',',$field['EXCEPT']);
            unset($field['EXCEPT']);
            if(!empty($field)){
                $delFields = array_keys($field);
                $fields = array_diff($this->getFields(),$except,$delFields);
                $fields += $field;
            }else{
                $fields = array_diff($this->getFields(),$except);
            }
            $this->_field($fields);
        }else $this->_field($field);
        return $this;
    }

    /**
     * 设置要操作的数据表
     * @param  [string] $table [description]
     * @return [type]        [description]
     */
    public function table($table=null){
        if($table && $table != $this->subTableName){
            $this->subTableName = $table;
            $this->table = $this->table_name($table);
            $this->primaryKey = null;
            $this->fields = null;
        }
        return $this;
    }

    /**
     * 设置表别名
     * @param  [string] $name [description]
     * @return [object]       [description]
     */
    public function alias($name=null){
        if($name) $this->table = '`' . self::$CONFIG['prefix'] . "{$this->subTableName}` AS {$name}";
        return $this;
    }

    /**
     * 设置where条件
     * @param  [type] $where [description]
     * @return [type]        [description]
     */
    public function where($where,$arr=null){
        if(empty($where)) return $this;
        if(is_array($where)){
            if(!self::is_assoc($where)){
                if(2 == count($where) && is_array($where[1])){
                    $this->logic[] = strtoupper(" {$where[0]}");
                    $this->where[] = $this->where_arr($where[1]);
                }else{
                    if(!$this->primaryKey) $this->getFields();
                    $this->logic[] = ' AND';
                    $this->where[] = $this->bind($this->primaryKey[0],$where,'IN');
                    return $this;
                }
            }else{
                $this->logic[] = ' AND';
                $this->where[] = $this->where_arr($where);
            }
            return $this;
        }elseif(is_numeric($where)){
            if(!$this->primaryKey) $this->getFields();
            $this->logic[] = ' AND';
            $this->where[] = $this->bind($this->primaryKey[0],$where);
            return $this;
        }else{
            $this->logic[] = ' AND';
            $this->where[] = $this->where_str($where,$arr);
            return $this;
        }
    }

    /**
     * 设置join条件
     * @param  [string] $join [description]
     * @return [object]       [description]
     */
    public function join($join=null,$direction="LEFT"){
        if(!$join) return $this;
        switch(gettype($join)){
            case 'string':
                $join = $this->tojoin($join,$direction);
                if($join) $this->join[] = $join;
                break;
            case 'array':
                foreach($join as $value){
                    $join = $this->tojoin($value,$direction);
                    if($join) $this->join[] = $join;
                }
                break;
        }
        return $this;
    }

    /**
     * 设置返回行数
     * @param  integer $start  [起始行]
     * @param  integer $number [中止行]
     * @return [object]        [description]
     */
    public function limit($start,$number=0){
        if($number){
            $this->limit = "LIMIT {$start},{$number}";
        }else{
            if(is_array($start)){
                $this->limit = "LIMIT {$start[0]},{$start[1]}";
            }
            else $this->limit = " LIMIT {$start}";
        }
        return $this;
    }

    /**
     * 设置排序
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    public function order($order){
        if(!$order) return $this;
        if(strstr($order,',')){
            $order = explode(',',$order);
            foreach($order as $k=>&$v){
                $order[$k] = self::set_field($v);
            }
            $order = implode(',',$order);
        }else{
            $order = self::set_field($order);
        }
        $this->order = "ORDER BY {$order}";
        return $this;
    }

    /**
     * 设置分组
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    public function group($field){
        if(!$field) return $this;
        if(strstr($field,',')){
            $field = explode(',',$field);
            foreach($field as $k=>&$v){
                $field[$k] = self::set_field($field);
            }
            $field = implode(',',$field);
        }else{
            $field = self::set_field($field);
        }
        $this->group = "GROUP BY {$field}";
        return $this;
    }

    /**
     * 聚合条件
     * @param  [type] $having [description]
     * @return [type]         [description]
     */
    public function having($having,$arr=null){
        if(!$having) return $this;
        switch(gettype($having)){
            case 'string' :
                $this->having = 'HAVING ' . $this->where_str($having,$arr);
                return $this;
                break;
            case 'array' :
                $this->having = 'HAVING ' . $this->where_arr($having);
                return $this;
                break;
            default :
                throw new \PDOException('having参数错误!');
                return $this;
                break;
        }
    }

    private function _field($field){
        if(is_array($field)){
            foreach($field as $k=>&$v){
                if(is_numeric($k)) $field_arr[] = self::set_field($v);
                else{
                    $k = self::set_field($k);
                    $field_arr[] = "{$k} AS `{$v}`";
                }
            }
            $this->field = implode(',',$field_arr);
        }else{
            $this->field = $field;
        }
    }

    private function tojoin($join,$direction="LEFT"){
        if(!stristr($join,'join')) $join = $direction." JOIN {$join}";
        $preg = '/(.+(JOIN|join)\s+)(\w+)\s+(.+)/';
        if(preg_match($preg,$join,$match)){
            $table = $this->table_name($match[3]);
            $join_sql = preg_replace($preg,"$1 $table $4",$join);
        }else{
            throw new \PDOException("join语句错误:{$join}");
            return false;
        }
        return $join_sql;
    }
    private function set_having($having){
        $having = trim($having);
        $preg = '/([\<\>\=])+|(\s+IN\s+)|(\s+NOT\s*IN\s+)/i';
        if(preg_match($preg,$having,$match)){
            $sp = $match[0];
            $having_arr = explode($sp,$having);
            $field = self::set_field(trim($having_arr[0]));
            $having = "{$field} {$sp} {$having_arr[1]}";
            return $having;
        }else{
            throw new \PDOException('having参数错误!');
        }
    }

    protected static function is_assoc($arr){//是否是关联数组
        return !is_numeric(implode('',array_keys($arr)));
    }
    protected static function bind_key($key){//绑定的参数名
        //return strstr($key,'.') ? ':' . str_replace('.','_',$key) : ":{$key}";
        //return ":{$key}";
        return ':'.str_replace(['(',')','.'],['','','_'],$key);
    }
    protected static function key($key){
        $preg = '#([\w\s]*)(\()?(\b\w+\.)?(\b\w+\b)#';
        return preg_replace($preg,'$1$2$3`$4`',$key);
    }
    protected static function bind_str($value){
        $preg = '/^\{(.+)\}$/';
        if(preg_match($preg,$value,$match)) return self::key($match[1]);
        else return false;
    }
    protected static function logic($str){
        $preg = '/(\&|and\s+|AND\s+|or\s+|OR\s+|\|)/';
        if(preg_match($preg,$str,$match)){
            $sp = trim($match[1]);
            switch($sp){
                case '&' :
                case 'and' :
                case 'AND' :
                    $sp = ' AND ';
                    break;
                case '|' :
                case 'OR' :
                case 'or' :
                    $sp = ' OR ';
                    break;
            }
            return [$match[1],$sp];
        }
        else return false;
    }
    protected static function operator($str){
        $preg = '/(\S+)\s*([\<\>\=\!]+)/';
        if(preg_match($preg,$str,$match)) return $match;
        else return false;
    }
    protected static function arr_operator($str){
        $preg = '/(\w+)\s+(in)|(IN)|(not\s*in)|(NOT\s*IN)/';
        if(preg_match($preg,$str,$match)) return $match;
        else return false;
    }
    protected static function check_key($key){
        $op = self::operator($key);
        $_key = $op[1] ?: null;
        $sp = self::logic($key);
        if($sp){
            $key_arr = explode($sp[0],$key);
            if(!$_key && empty($key_arr[0])) $_key = $key_arr[1];
            else{
                $return['sp'] = $sp[0];
                $_key = $_key ?: $key_arr;
            }
        }
        $return['key'] = $_key ?: $key;
        $return['operator'] = $op[2] ?: false;
        $return['logic'] = $sp[1] ?: false;
        return $return;
    }
    protected static function check_like($value){
        $preg = '/^\%(.+)\%$/';
        if(preg_match($preg,trim($value),$match)) return $match[1];
        else return false;
    }
    protected static function check_value($value){
        $logic_arr = ['and','AND','or','OR'];
        $operator_arr = ['=','<','<=','>','>=','<>','in','IN','not in','NOT IN','between','BETWEEN','not between','NOT BETWEEN','like','not like','LIKE','NOT LIKE'];
        if(!is_array($value)){
            if($like = self::check_like($value)) $operator = 'LIKE';
            $val = $value;
        }
        elseif(in_array($value[0],$logic_arr)){
            $logic = $value[0];
            $val = $value[1];
        }
        elseif(in_array($value[0],$operator_arr)){
            $operator = $value[0];
            $val = $value[1];
        }else{
            $val = $value;
        }
        $return['logic'] = empty($logic) ? false : strtoupper($logic);
        $return['operator'] = empty($operator) ? false : strtoupper($operator);
        $return['value'] = $val;
        return $return;
    }
    protected static function set_field($field){
        $preg = '/^(\w+)$/';
        if(preg_match($preg,$field)) return "`{$field}`";
        $preg = '/^(\w+)\s+(\w+)$/';
        if(preg_match($preg,$field)) return preg_replace($preg,"`$1` $2",$field);
        $preg = '/(\w+\.)(\w+)/';
        if(preg_match($preg,$field)) return preg_replace($preg,"$1`$2`",$field);
        $preg = '/\((\w+)\)/';
        if(preg_match($preg,$field)) return preg_replace($preg,"(`$1`)",$field);
        return $field;
    }

    protected function cl(){
        $this->order = null;
        $this->args = $this->bind_value;
        $this->where = null;
        $this->logic = null;
        $this->bind_value = null;
        $this->join = null;
        $this->deleteTable = null;
        $this->tables = null;
        $this->field = '*';
        $this->having = null;
        $this->group = null;
        $this->r = 0;
        $this->limit = null;
    }
    protected function bind_value($name,$value=null){
        $this->bind_value[$name] = $value;
    }
    protected function bind($key,$value,$operator='='){
        $key = trim($key);
        $bind_key = self::bind_key($key);
        $_key = self::key($key);
        if(is_array($value)){
            $between_arr = ['between','BETWEEN','not between','NOT BETWEEN'];
            if(in_array($operator,$between_arr)){
                $bind_key1 = "{$bind_key}_" . ($this->r ++);
                $this->bind_value[$bind_key1] = $value[0];
                $bind_key2 = "{$bind_key}_" . ($this->r ++);
                $this->bind_value[$bind_key2] = $value[1];
                $where = "{$_key} {$operator} {$bind_key1} AND {$bind_key2}";
            }else{
                foreach($value as $k=>&$v){
                    $sub_key_arr[] = $sub_key = "{$bind_key}_" . ($this->r ++);
                    $this->bind_value[$sub_key] = $v;
                }
                $where = "{$_key} {$operator}" . ' (' . implode(',',$sub_key_arr) . ')';
            }
        }else{
            $key_str = self::bind_str($value);//参数被{}包裹代表是字段名，不绑定参数。
            if($key_str){
                $where = "{$_key} {$operator} {$key_str}";
                return $where;
            }
            if(isset($this->bind_value[$bind_key])) $bind_key = "{$bind_key}_" . (++$this->r);
            $where = "{$_key} {$operator} {$bind_key}";
            $this->bind_value[$bind_key] = $value;
        }
        return $where;
    }
    protected function table_name($table){
        $table = trim($table);
        if(strstr($table,',')){
            $table = explode(',',$table);
            foreach($table as &$v){
                $v = trim($v);
                if(strstr($v,' ')){
                    $tableName_arr = explode(' ',$v);
                    $tableName = array_shift($tableName_arr);
                    if(!in_array($tableName,$this->tables)) $this->tables[] = $tableName;
                    $tableArr[] = "{$tableName}` " . implode(' ',$tableName_arr);
                }else{
                    if(!in_array($v,$this->tables)) $this->tables[] = $v;
                    $tableArr[] = "{$v}`";
                }
            }
            $table_name = '`' . self::$CONFIG['prefix'] . implode(',`' . self::$CONFIG['prefix'],$tableArr);
        }else{
            if(strstr($table,' ')){
                $tableName_arr = explode(' ',$table);
                $this->subTableName = array_shift($tableName_arr);
                if(!$this->tables || !in_array($this->subTableName,$this->tables)) $this->tables[] = $this->subTableName;
                $table = "{$this->subTableName}` " . implode(' ',$tableName_arr);
                $table_name = '`' . self::$CONFIG['prefix'] . $table;
            }else{
                $table_name = '`' . self::$CONFIG['prefix'] . "{$table}`";
                if(!$this->tables || !in_array($table,$this->tables)) $this->tables[] = $table;
            }
        }
        return $table_name;
    }
    protected function where_arr($where){
        $sql = '';
        foreach($where as $vk=>&$vv){
            $v = self::check_value($vv);
            $k = self::check_key($vk);
            $key = $k['key'];
            $value = $v['value'];
            $logic = $v['logic'] ?: $k['logic'];
            if('LIKE' == $v['operator'] && ('<>' == $k['operator'] || '!=' == $k['operator'])) $operator = 'NOT LIKE';
            else $operator = $v['operator'] ?: $k['operator'];
            $logic = $logic ?: 'AND';
            if(is_array($value)){
                if(!$value) continue;
                $operator = $operator && in_array($operator,['<>','!=']) ? 'NOT IN' : 'IN';
            }else{
                if(strstr($operator,'LIKE') && !self::check_like($value)) $value = '%' . $value . '%';
                else $operator = $operator ?: '=';
            }
            if(!empty($k['sp']) && is_array($key)){
                foreach($key as &$kk){
                    $subSql[] = $this->bind($kk,$value,$operator);
                }
                $sql .= ' AND (' . implode(" {$logic} ",$subSql) . ')';
            }else{
                $sql .= " {$logic} " . $this->bind($key,$value,$operator);
            }
        }
        return ltrim($sql,' ANDOR');
    }
    protected function where_str($where,$arr=null){
        if($arr){
            foreach($arr as $k=>&$v){
                if(isset($this->bind_value[$k])){
                    $bind_key = "{$bind_key}_" . (++$this->r);
                    $find[] = $k;
                    $replace[] = $bind_key;
                }else $bind_key = $k;
                $this->bind_value[$bind_key] = $v;
                //绑定参数
            }
        }
        return (!empty($find) && !empty($replace)) ? str_replace($find,$replace,$where) : $where;
    }
    protected function add_update($add,$safe=true){
        if(is_array($add)){
            //$preg = '/\{(.+)\}/';
            $fields = $this->getFields();
            $preg = '/\{('.implode('|',$fields).')\}(\s*[\+\-\*\/\%\|])/';
            foreach($add as $key=>&$value){
                if(!in_array($key,$fields)) continue;
                if($safe) $value = htmlspecialchars($value,ENT_QUOTES);
                if(preg_match($preg,$value)){
                    $bind_key = preg_replace($preg,'`\1`\2',$value);
                }else{
                    $bind_key = $this->bind_key($key);
                    if(isset($this->bind_value[$bind_key])) $bind_key = "{$bind_key}_" . (++$this->r);
                    $this->bind_value($bind_key,$value);
                }
                $key = self::key($key);
                $sql[] = "{$key}={$bind_key}";
            }
            return $sql;
        }else{
            throw new \PDOException('写入参数错误!');
        }
    }
    protected function sql_where(){
        if(empty($this->where)) return null;
        if(1 < count($this->where)){
            $sql = " WHERE ";
            $this->logic[0] = '';
            foreach($this->where as $k=>&$v){
                $sql .= "{$this->logic[$k]} ({$v})";
            }
            return $sql;
        }
        else $where = $this->where[0];
        return " WHERE {$where}";
    }
    protected function SQL($field=false){
        $field = $field ?: $this->field;
        $where = $this->sql_where();
        $join = $this->join ? ' ' . implode(' ',$this->join) : '';
        $limit = $this->limit ? ' ' . $this->limit : '';
        $order = $this->order ? ' ' . $this->order : '';
        $group = $this->group ? ' ' . $this->group : '';
        $having = $this->having ? ' ' . $this->having : '';
        return "SELECT {$field} FROM {$this->table}{$join}{$where}{$group}{$having}{$order}{$limit}";
    }
}