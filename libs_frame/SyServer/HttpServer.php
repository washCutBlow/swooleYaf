<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2019/9/6 0006
 * Time: 9:02
 */
namespace SyServer;

use Response\Result;
use Response\SyResponseHttp;
use SyConstant\ErrorCode;
use SyConstant\Server;
use SyTrait\Server\FrameHttpTrait;
use SyTrait\Server\FramePreProcessHttpTrait;
use SyTrait\Server\ProjectHttpTrait;
use SyTrait\Server\ProjectPreProcessHttpTrait;
use Tool\Tool;
use Yaf\Registry;
use Yaf\Request\Http;

class HttpServer extends BaseServer
{
    use FrameHttpTrait;
    use ProjectHttpTrait;
    use FramePreProcessHttpTrait;
    use ProjectPreProcessHttpTrait;

    const RESPONSE_RESULT_TYPE_FORBIDDEN = 0; //响应结果类型-拒绝请求
    const RESPONSE_RESULT_TYPE_ACCEPT = 1; //响应结果类型-允许请求执行业务

    /**
     * swoole请求cookie域名数组
     * @var array
     */
    private $_reqCookieDomains = [];
    /**
     * HTTP响应
     * @var \swoole_http_response
     */
    private static $_response = null;
    /**
     * 响应消息
     * @var string
     */
    private static $_rspMsg = '';
    /**
     * swoole请求头信息数组
     * @var array
     */
    private static $_reqHeaders = [];
    /**
     * swoole服务器信息数组
     * @var array
     */
    private static $_reqServers = [];

    public function __construct(int $port)
    {
        parent::__construct($port);
        $projectLength = strlen(SY_PROJECT);
        // project.deva01.modules.api.type
        $serverType = Tool::getConfig('project.' . SY_ENV . SY_PROJECT . '.modules.' . substr(SY_MODULE, $projectLength) . '.type');
        if ($serverType != Server::SERVER_TYPE_API_GATE) {
            exit("服务端类型不支持" . PHP_EOL);
        }
        define('SY_SERVER_TYPE', $serverType);
        $this->_configs['server']['cachenum']['modules'] = 1;///(int)Tool::getArrayVal($this->_configs, 'server.cachenum.modules', 0, true);

        $this->checkServerHttp();
        $this->_reqCookieDomains = Tool::getConfig('project.' . SY_ENV . SY_PROJECT . '.domain.cookie');
    }

    private function __clone()
    {
    }

    public function start()
    {
        $this->initTableHttp();
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
     */
    public function onWorkerError(\swoole_server $server, $workId, $workPid, $exitCode)
    {
        $this->basicWorkError($server, $workId, $workPid, $exitCode);

        if (self::$_response) {
            $this->setRspCookies(self::$_response, Registry::get(Server::REGISTRY_NAME_RESPONSE_COOKIE));
            $this->setRspHeaders(self::$_response, Registry::get(Server::REGISTRY_NAME_RESPONSE_HEADER));

            $json = new Result();
            $json->setCodeMsg(ErrorCode::COMMON_SERVER_ERROR, ErrorCode::getMsg(ErrorCode::COMMON_SERVER_ERROR));
            self::$_response->end($json->getJson());
        }
    }

    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        self::$_response = $response;
        $initRes = $this->initReceive($request);
        if (strlen($initRes) > 0) {
            self::$_rspMsg = $initRes;
        } else {
            $rspHeaders = [];
            $handleHeaderRes = $this->handleReqHeader($rspHeaders);
            if ($handleHeaderRes == self::RESPONSE_RESULT_TYPE_ACCEPT) {
                self::$_rspMsg = $this->handleReqService($request, $rspHeaders);
                $this->setRspCookies($response, Registry::get(Server::REGISTRY_NAME_RESPONSE_COOKIE));
                $this->setRspHeaders($response, Registry::get(Server::REGISTRY_NAME_RESPONSE_HEADER));
            } else {
                $rspHeaders['Content-Type'] = 'text/plain; charset=utf-8';
                $rspHeaders['Syresp-Status'] = 403;
                $this->setRspHeaders($response, $rspHeaders);
            }
        }

        $response->end(self::$_rspMsg);
        $this->clearRequest();
    }

    /**
     * 设置响应头信息
     * @param \swoole_http_response $response
     * @param array|bool $headers
     */
    private function setRspHeaders(\swoole_http_response $response, $headers)
    {
        if (is_array($headers)) {
            if (!isset($headers['Content-Type'])) {
                $response->header('Content-Type', 'application/json; charset=utf-8');
            }

            foreach ($headers as $headerName => $headerVal) {
                $response->header($headerName, $headerVal);
            }

            if (isset($headers['Location'])) {
                $response->status(302);
            } elseif (isset($headers['Syresp-Status'])) {
                $response->status($headers['Syresp-Status']);
            }
        } else {
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Content-Type', 'application/json; charset=utf-8');
        }
    }

    /**
     * 设置响应cookie信息
     * @param \swoole_http_response $response
     * @param array|bool $cookies
     */
    private function setRspCookies(\swoole_http_response $response, $cookies)
    {
        if (is_array($cookies)) {
            foreach ($cookies as $cookie) {
                $value = Tool::getArrayVal($cookie, 'value', null);
                $expires = Tool::getArrayVal($cookie, 'expires', 0);
                $path = Tool::getArrayVal($cookie, 'path', '/');
                $domain = Tool::getArrayVal($cookie, 'domain', '');
                $secure = Tool::getArrayVal($cookie, 'secure', false);
                $httpOnly = Tool::getArrayVal($cookie, 'httponly', false);
                $response->cookie($cookie['key'], $value, $expires, $path, $domain, $secure, $httpOnly);
            }
        }
    }

    /**
     * 初始化公共数据
     * @param \swoole_http_request $request
     * @return string
     */
    private function initReceive(\swoole_http_request $request)
    {
        $_POST = $request->post ?? [];
        $_SESSION = [];
        Registry::del(Server::REGISTRY_NAME_SERVICE_ERROR);
        self::$_reqHeaders = $request->header ?? [];
        self::$_reqServers = $request->server ?? [];
        self::$_rspMsg = '';

        if (isset($request->header['content-type']) && ($request->header['content-type'] == 'application/json')) {
            $_POST = Tool::jsonDecode($request->rawContent());
            if (!is_array($_POST)) {
                $res = new Result();
                $res->setCodeMsg(ErrorCode::COMMON_SERVER_ERROR, 'JSON格式不正确');
                return $res->getJson();
            }
        }

        $_SERVER = [];
        foreach (self::$_reqServers as $key => $val) {
            $_SERVER[strtoupper($key)] = $val;
        }
        foreach (self::$_reqHeaders as $key => $val) {
            $_SERVER[strtoupper($key)] = $val;
        }
        if (!isset($_SERVER['HTTP_HOST'])) {
            $_SERVER['HTTP_HOST'] = $this->_host . ':' . $this->_port;
        }
        if (!isset($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = '/';
        }

        $nowTime = time();
        $_SERVER[Server::SERVER_DATA_KEY_TIMESTAMP] = $nowTime;
        $_SERVER['SYREQ_ID'] = hash('md4', $nowTime . Tool::createNonceStr(8));
        return '';
    }

    private function initRequest(\swoole_http_request $request, array $rspHeaders)
    {
        self::$_reqStartTime = microtime(true);
        self::$_syServer->incr(self::$_serverToken, 'request_times', 1);
        $_GET = $request->get ?? [];
        $_FILES = $request->files ?? [];
        $_COOKIE = $request->cookie ?? [];
        $GLOBALS['HTTP_RAW_POST_DATA'] = $request->rawContent();
        //注册全局信息
        Registry::set(Server::REGISTRY_NAME_REQUEST_HEADER, self::$_reqHeaders);
        Registry::set(Server::REGISTRY_NAME_REQUEST_SERVER, self::$_reqServers);
        Registry::set(Server::REGISTRY_NAME_RESPONSE_HEADER, $rspHeaders);
        Registry::set(Server::REGISTRY_NAME_RESPONSE_COOKIE, []);
    }

    /**
     * 清理请求数据
     */
    private function clearRequest()
    {
        $_GET = [];
        $_POST = [];
        $_FILES = [];
        $_COOKIE = [];
        $_SERVER = [];
        $_SESSION = [];
        $GLOBALS['HTTP_RAW_POST_DATA'] = '';
        self::$_reqHeaders = [];
        self::$_reqServers = [];
        self::$_response = null;
        self::$_rspMsg = '';

        //清除yaf注册常量
        Registry::del(Server::REGISTRY_NAME_REQUEST_HEADER);
        Registry::del(Server::REGISTRY_NAME_REQUEST_SERVER);
        Registry::del(Server::REGISTRY_NAME_RESPONSE_HEADER);
        Registry::del(Server::REGISTRY_NAME_RESPONSE_COOKIE);

        self::$_syServer->set(self::$_serverToken, [
            'memory_usage' => memory_get_usage(),
        ]);
    }

    /**
     * 处理请求头
     * @param array $headers 响应头配置
     * @return int
     */
    private function handleReqHeader(array &$headers) : int
    {
        $domainTag = $_SERVER['SY-DOMAIN'] ?? 'base';
        $cookieDomain = $this->_reqCookieDomains[$domainTag] ?? null;
        if (is_null($cookieDomain)) {
            return self::RESPONSE_RESULT_TYPE_FORBIDDEN;
        }
        $_SERVER['SY-DOMAIN'] = $cookieDomain;
        return self::RESPONSE_RESULT_TYPE_ACCEPT;
    }

    /**
     * 处理请求业务
     * @param \swoole_http_request $request
     * @param array $initRspHeaders 初始化响应头
     * @return string
     */
    private function handleReqService(\swoole_http_request $request, array $initRspHeaders) : string
    {
        $uri = Tool::getArrayVal(self::$_reqServers, 'request_uri', '/');
        $uriCheckRes = $this->checkRequestUri($uri);
        if (strlen($uriCheckRes['error']) > 0) {
            return $uriCheckRes['error'];
        }
        $uri = $uriCheckRes['uri'];
        self::$_reqServers['request_uri'] = $uriCheckRes['uri'];

        $funcName = $this->getPreProcessFunction($uri, $this->preProcessMapFrame, $this->preProcessMapProject);
        if (is_bool($funcName)) {
            $error = new Result();
            $error->setCodeMsg(ErrorCode::COMMON_SERVER_ERROR, '预处理函数命名不合法');
            $result = $error->getJson();
            unset($error);
            return $result;
        } elseif (strlen($funcName) > 0) {
            return $this->$funcName($request);
        }

        $this->initRequest($request, $initRspHeaders);

        $error = null;
        $result = '';
        $httpObj = new Http($uri);
        try {
            $result = $this->_app->bootstrap()->getDispatcher()->dispatch($httpObj)->getBody();
            if (strlen($result) == 0) {
                $error = new Result();
                $error->setCodeMsg(ErrorCode::SWOOLE_SERVER_NO_RESPONSE_ERROR, '未设置响应数据');
            }
        } catch (\Exception $e) {
            SyResponseHttp::header('Content-Type', 'application/json; charset=utf-8');
            if (SY_REQ_EXCEPTION_HANDLE_TYPE) {
                $error = $this->handleReqExceptionByFrame($e);
            } else {
                $error = $this->handleReqExceptionByProject($e);
            }
        } finally {
            self::$_syServer->decr(self::$_serverToken, 'request_handling', 1);
            unset($httpObj);
            if (is_object($error)) {
                $result = $error->getJson();
                unset($error);
            }
        }

        return $result;
    }
}
