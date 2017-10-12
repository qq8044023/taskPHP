<?php
// +----------------------------------------------------------------------
// | core\libPHP [ WE CAN DO IT JUST core\lib ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://core\libphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com> 
// +----------------------------------------------------------------------

namespace core\lib\model;

use core\lib\Model;

class Pivot extends Model
{

    /** @var Model */
    public $parent;

    protected $autoWriteTimestamp = false;

    /**
     * 架构函数
     * @access public
     * @param Model         $parent 上级模型
     * @param array|object  $data 数据
     * @param string        $table 中间数据表名
     */
    public function __construct(Model $parent = null, $data = [], $table = '')
    {
        $this->parent = $parent;

        if (is_null($this->name)) {
            $this->name = $table;
        }

        parent::__construct($data);

        $this->class = $this->name;
    }

}
