<?php
namespace tasks\demo\model;
use core\lib\Model;
/**
 * 测试模型
 */
class gameActivity extends Model{
    //读取 tourism_game_activity 表
    public function test(){
        return $this->where('status',1)->find();
    }
    //读取 tourism_game_common_game 表
    public function test1(){
        return $this->table("tourism_game_common_game")->where('status',1)->find();
    }
}
 