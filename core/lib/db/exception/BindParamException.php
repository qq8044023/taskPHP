<?php
// +----------------------------------------------------------------------
// | core\libPHP [ WE CAN DO IT JUST core\lib ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://core\libphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://zjzit.cn>  
// +----------------------------------------------------------------------

namespace core\lib\db\exception;

use core\lib\exception\DbException;

/**
 * PDO参数绑定异常
 */
class BindParamException extends DbException
{

    /**
     * BindParamException constructor.
     * @param string $message
     * @param array  $config
     * @param string $sql
     * @param array    $bind
     * @param int    $code
     */
    public function __construct($message, $config, $sql, $bind, $code = 10502)
    {
        $this->setData('Bind Param', $bind);
        parent::__construct($message, $config, $sql, $code);
    }
}
