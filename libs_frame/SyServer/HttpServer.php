<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2019/9/6 0006
 * Time: 9:02
 */
namespace SyServer;

class HttpServer extends BaseServer
{
    public function __construct()
    {
        parent::__construct();
    }

    private function __clone()
    {
    }

    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $response->header('Content-Type', 'text/html; charset=utf-8');
        $response->end('<h1>Hello Swoole. #' . rand(1000, 9999) . '</h1>');
    }

    public function start()
    {
        $this->_server = new \swoole_http_server("0.0.0.0", 8800);
        $this->baseStart([
            'request' => 'onRequest',
        ]);
    }
}
