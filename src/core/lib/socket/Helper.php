<?php
namespace core\lib\socket;
/**
 * 事件回调接口
 * @author cqcqphper 小草<cqcqphper@163.com>
 */
trait Helper{
    /**
     * 连接句柄
     * @var unknown
     */
    public $connect=null;
    /**
     * 新用户连接时触发的回调
     * @var unknown
     */
    public $onConnect=null;
    /**
     * 当客户端发来消息时触发的回调
     * @var unknown
     */
    public $onMessage=null;
    
    /**
     * 当用户断开连接时触发的回调
     * @var unknown
     */
    public $onClose=null;
    
}