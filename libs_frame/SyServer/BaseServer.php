<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2019/9/6 0006
 * Time: 9:03
 */
namespace SyServer;

abstract class BaseServer
{
    /**
     * 请求服务对象
     * @var \swoole_http_server|\swoole_server
     */
    protected $_server = null;

    public function __construct()
    {
    }

    private function __clone()
    {
    }

    protected function baseStart(array $registerMap)
    {
        //绑定注册方法
        foreach ($registerMap as $eventName => $funcName) {
            $this->_server->on($eventName, [$this, $funcName]);
        }

        $this->_server->start();
    }
}
