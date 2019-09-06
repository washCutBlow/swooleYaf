<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2019/9/6 0006
 * Time: 9:02
 */
namespace SyServer;

use Log\Log;

class HttpServer extends BaseServer
{
    public function __construct(int $port)
    {
        parent::__construct($port);
    }

    private function __clone()
    {
    }

    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        Log::log('aabbcc');
        $response->header('Content-Type', 'text/html; charset=utf-8');
        $response->end('<h1>Hello Swoole. #' . $this->_port . '</h1>');
    }

    public function start()
    {
        $this->_server = new \swoole_http_server($this->_host, $this->_port);
        $this->baseStart([
            'start' => 'onStart',
            'managerStart' => 'onManagerStart',
            'workerStart' => 'onWorkerStart',
            'workerStop' => 'onWorkerStop',
            'workerError' => 'onWorkerError',
            'workerExit' => 'onWorkerExit',
            'shutdown' => 'onShutdown',
            'request' => 'onRequest',
            'close' => 'onClose',
        ]);
    }

    public function onStart(\swoole_server $server)
    {
        $this->basicStart($server);
    }

    public function onWorkerStop(\swoole_server $server, int $workerId)
    {
        $this->basicWorkStop($server, $workerId);
    }

    /**
     * @param \swoole_server $server
     * @param int $workId
     * @param int $workPid
     * @param int $exitCode
     * @todo 集成错误处理
     */
    public function onWorkerError(\swoole_server $server, $workId, $workPid, $exitCode)
    {
        $this->basicWorkError($server, $workId, $workPid, $exitCode);

//        if (self::$_response) {
//            $this->setRspCookies(self::$_response, Registry::get(Server::REGISTRY_NAME_RESPONSE_COOKIE));
//            $this->setRspHeaders(self::$_response, Registry::get(Server::REGISTRY_NAME_RESPONSE_HEADER));
//
//            $json = new Result();
//            $json->setCodeMsg(ErrorCode::COMMON_SERVER_ERROR, ErrorCode::getMsg(ErrorCode::COMMON_SERVER_ERROR));
//            if (self::$_reqTag) {
//                self::$_response->end($json->getJson());
//            } else {
//                self::$_response->end($json->getJson() . Server::SERVER_HTTP_TAG_RESPONSE_EOF);
//            }
//        }
    }
}
