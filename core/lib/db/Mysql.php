<?php
namespace core\lib\db;
use core\lib\db\Extend\MysqlClient;
/**
 * 
 * 码农<8044023@qq.com>,cqcqphper 小草<cqcqphper@163.com>
 **/
class Mysql extends ClientSire{
    /**
     * 数据库连接
     *   */
    public function connect($config){
        $this->Db=new MysqlClient(
                $config["db_host"],
                $config["db_prot"], 
                $config["db_username"], 
                $config["db_password"], 
                $config["db_name"]
        );
    }
    /**
     * 查询一条
     * {@inheritDoc}
     * @see \core\lib\db\ClientSire::find()
     *  */
    public function find(){
        return $this->parseQuerySql()->row();
    }
    /**
     * 查询 数据列表
     * {@inheritDoc}
     * @see \core\lib\db\ClientSire::select()  */
    public function select(){
        $query=$this->parseQuerySql();
        if ($this->limit!=null){ 
            return $query->limit($this->limit)->query();
        }
        return $query->query();
    }
    /**
     * 删除数据
     * {@inheritDoc}
     * @see \core\lib\db\ClientSire::delete()  */
    public function delete(){
        if (is_array($this->where)){
            foreach ($this->where as $k=>$v){
                $where.=" ".$k."'".$v."' AND";
            }
            $where=substr($where,0,strlen($where)-3);
        }else{
            !is_null($this->where)?$this->where:"1=1";
        }
       return $this->Db->delete($this->name)->where($where)->query();
    }
    /**
     * 修改 数据
     * {@inheritDoc}
     * @see \core\lib\db\ClientSire::save()  */
    public function save($cols=[]){
        $this->wheres();
        return $this->Db->update($this->name)->cols($cols)->where($this->where)->query();
    }
    /**
     * 新增
     * {@inheritDoc}
     * @see \core\lib\db\ClientSire::add()  */
    public function add($data=[]){
        return $this->Db->insert($this->name)->cols($data)->query();
    }
    /**
     * 打印sql语句
     * {@inheritDoc}
     * @see \core\lib\db\ClientSire::getLastSql()  */
    public function getLastSql(){
        $this->Db->lastSQL();
    }
    /**
     * 直接操作 底层封装类
     * {@inheritDoc}
     * @see \core\lib\db\ClientSire::model()  */
    public function model(){
        return $this->Db;
    }
    /**
     * 处理拼接sql
     * 
     *   */
    protected function parseQuerySql(){
        $this->wheres();
        return $this->Db->select($this->filed)->from($this->name)->where($this->where); 
    }
    /**
     * 处理where
     *   */
    protected function wheres(){
        if (is_array($this->where)){
            foreach ($this->where as $k=>$v){
                $this->where[$k]=intval($v);
            }
        }
    }
};