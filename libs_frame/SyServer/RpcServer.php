<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2019/9/6 0006
 * Time: 9:03
 */
namespace SyServer;

class RpcServer extends BaseServer
{
    public function __construct(int $port)
    {
        parent::__construct($port);
    }

    private function __clone()
    {
    }
}
