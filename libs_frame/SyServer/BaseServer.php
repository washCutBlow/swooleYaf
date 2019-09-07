<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2019/9/6 0006
 * Time: 9:03
 */
namespace SyServer;

use Log\Log;
use Response\Result;
use SyConstant\ErrorCode;
use SyConstant\Project;
use SyConstant\Server;
use SyTrait\Server\FrameBaseTrait;
use SyTrait\Server\ProjectBaseTrait;
use Tool\Dir;
use Tool\Tool;
use Yaf\Application;

abstract class BaseServer
{
    use FrameBaseTrait;
    use ProjectBaseTrait;

    /**
     * 请求服务对象
     * @var \swoole_http_server|\swoole_server
     */
    protected $_server = null;
    /**
     * 配置数组
     * @var array
     */
    protected $_configs = [];
    /**
     * 请求域名
     * @var string
     */
    protected $_host = '';
    /**
     * 请求端口
     * @var int
     */
    protected $_port = 0;
    /**
     * pid文件
     * @var string
     */
    protected $_pidFile = '';
    /**
     * 提示文件
     * @var string
     */
    protected $_tipFile = '';
    /**
     * 服务token码,用于标识不同的服务,每个服务的token不一样
     * @var string
     */
    protected static $_serverToken = '';
    /**
     * 请求开始毫秒级时间戳
     * @var float
     */
    protected static $_reqStartTime = 0.0;
    /**
     * @var \Yaf\Application
     */
    protected $_app = null;

    public function __construct(int $port)
    {
        if (($port <= Server::ENV_PORT_MIN) || ($port > Server::ENV_PORT_MAX)) {
            exit('端口不合法' . PHP_EOL);
        }

        $this->checkSystemEnv();
        $this->_configs = Tool::getConfig('syserver.' . SY_ENV . SY_MODULE);

        define('SY_SERVER_IP', $this->_configs['server']['host']);

        $this->_configs['server']['port'] = $port;
        //关闭协程
        $this->_configs['swoole']['enable_coroutine'] = false;
        //日志
        $this->_configs['swoole']['log_level'] = SWOOLE_LOG_INFO;
        //开启TCP快速握手特性,可以提升TCP短连接的响应速度
        $this->_configs['swoole']['tcp_fastopen'] = true;
        //启用异步安全重启特性,Worker进程会等待异步事件完成后再退出
        $this->_configs['swoole']['reload_async'] = true;
        //进程最大等待时间,单位为秒
        $this->_configs['swoole']['max_wait_time'] = 60;
        //dispatch_mode=1或3后,系统无法保证onConnect/onReceive/onClose的顺序,因此可能会有一些请求数据在连接关闭后才能到达Worker进程
        //设置为false表示无论连接是否关闭Worker进程都会处理数据请求
        $this->_configs['swoole']['discard_timeout_request'] = false;
        //设置请求数据尺寸
        $this->_configs['swoole']['open_length_check'] = true;
        $this->_configs['swoole']['package_max_length'] = Project::SIZE_SERVER_PACKAGE_MAX;
        $this->_configs['swoole']['socket_buffer_size'] = Project::SIZE_CLIENT_SOCKET_BUFFER;
        $this->_configs['swoole']['buffer_output_size'] = Project::SIZE_CLIENT_BUFFER_OUTPUT;
        //设置线程数量
        $execRes = Tool::execSystemCommand('cat /proc/cpuinfo | grep "processor" | wc -l');
        $this->_configs['swoole']['reactor_num'] = (int)(2 * $execRes['data'][0]);
        $this->_host = $this->_configs['server']['host'];
        $this->_port = $this->_configs['server']['port'];
        $this->_pidFile = SY_ROOT . '/pidfile/' . SY_MODULE . $this->_port . '.pid';
        $this->_tipFile = SY_ROOT . '/tipfile/' . SY_MODULE . $this->_port . '.txt';
        Dir::create(SY_ROOT . '/tipfile/');
        if (is_dir($this->_tipFile)) {
            exit('提示文件不能是文件夹' . PHP_EOL);
        } elseif (!file_exists($this->_tipFile)) {
            $tipFileObj = fopen($this->_tipFile, 'wb');
            if (is_bool($tipFileObj)) {
                exit('创建或打开提示文件失败' . PHP_EOL);
            }
            fwrite($tipFileObj, '');
            fclose($tipFileObj);
        }

        //生成服务唯一标识
        self::$_serverToken = hash('crc32b', $this->_configs['server']['host'] . ':' . $this->_configs['server']['port']);

        //设置日志目录
        Log::setPath(SY_LOG_PATH);
    }

    private function __clone()
    {
    }

    private function checkSystemEnv()
    {
        if (PHP_INT_SIZE < 8) {
            exit('操作系统必须是64位' . PHP_EOL);
        }
        if (version_compare(PHP_VERSION, Server::VERSION_MIN_PHP, '<')) {
            exit('PHP版本必须大于等于' . Server::VERSION_MIN_PHP . PHP_EOL);
        }
        if (!defined('SY_MODULE')) {
            exit('模块名称未定义' . PHP_EOL);
        }
        if (!in_array(SY_ENV, Server::$totalEnvProject, true)) {
            exit('环境类型不合法' . PHP_EOL);
        }

        $os = php_uname('s');
        if (!in_array($os, Server::$totalEnvSystem, true)) {
            exit('操作系统不支持' . PHP_EOL);
        }

        //检查必要的扩展是否存在
        $extensionList = [
            'yac',
            'yaf',
            'PDO',
            'pcre',
            'pcntl',
            'redis',
            'yaconf',
            'swoole',
            'SeasLog',
            'msgpack',
        ];
        foreach ($extensionList as $extName) {
            if (!extension_loaded($extName)) {
                exit('扩展' . $extName . '未加载' . PHP_EOL);
            }
        }

        if (version_compare(SWOOLE_VERSION, Server::VERSION_MIN_SWOOLE, '<')) {
            exit('swoole版本必须大于等于' . Server::VERSION_MIN_SWOOLE . PHP_EOL);
        }
        if (version_compare(SEASLOG_VERSION, Server::VERSION_MIN_SEASLOG, '<')) {
            exit('seaslog版本必须大于等于' . Server::VERSION_MIN_SEASLOG . PHP_EOL);
        }
        if (version_compare(YAC_VERSION, Server::VERSION_MIN_YAC, '<')) {
            exit('yac版本必须大于等于' . Server::VERSION_MIN_YAC . PHP_EOL);
        }
        if (version_compare(\YAF\VERSION, Server::VERSION_MIN_YAF, '<')) {
            exit('yaf版本必须大于等于' . Server::VERSION_MIN_YAF . PHP_EOL);
        }
    }

    /**
     * 开启服务
     */
    abstract public function start();

    /**
     * 帮助信息
     */
    public function help()
    {
        print_r('帮助信息' . PHP_EOL);
        print_r('-s 操作类型: restart-重启 stop-关闭 start-启动 kz-清理僵尸进程 startstatus-启动状态' . PHP_EOL);
        print_r('-n 项目名' . PHP_EOL);
        print_r('-module 模块名' . PHP_EOL);
        print_r('-port 端口,取值范围为1024-65535' . PHP_EOL);
    }

    /**
     * 关闭服务
     */
    public function stop()
    {
        if (is_file($this->_pidFile) && is_readable($this->_pidFile)) {
            $pid = (int)file_get_contents($this->_pidFile);
        } else {
            $pid = 0;
        }

        $msg = ' \e[1;31m \t[fail]';
        if ($pid > 0) {
            if (\swoole_process::kill($pid)) {
                $msg = ' \e[1;32m \t[success]';
            }
            file_put_contents($this->_pidFile, '');
        }
        system('echo -e "\e[1;36m stop ' . SY_MODULE . ': \e[0m' . $msg . ' \e[0m"');
        exit();
    }

    /**
     * 清理僵尸进程
     */
    public function killZombies()
    {
        //清除僵尸进程
        $commandZombies = 'ps -A -o pid,ppid,stat,cmd|grep ' . SY_MODULE . '|awk \'{if(($3 == "Z") || ($3 == "z")) print $1}\'';
        $execRes = Tool::execSystemCommand($commandZombies);
        if (($execRes['code'] == 0) && !empty($execRes['data'])) {
            system('kill -9 ' . implode(' ', $execRes['data']));
        }

        //清除worker中断进程
        $commandWorkers = 'ps -A -o pid,ppid,stat,cmd|grep ' . Server::PROCESS_TYPE_WORKER . SY_MODULE . '|awk \'{if($2 == "1") print $1}\'';
        $execRes = Tool::execSystemCommand($commandWorkers);
        if (($execRes['code'] == 0) && !empty($execRes['data'])) {
            system('kill -9 ' . implode(' ', $execRes['data']));
        }

        //清除task中断进程
        $commandTasks = 'ps -A -o pid,ppid,stat,cmd|grep ' . Server::PROCESS_TYPE_TASK . SY_MODULE . '|awk \'{if($2 == "1") print $1}\'';
        $execRes = Tool::execSystemCommand($commandTasks);
        if (($execRes['code'] == 0) && !empty($execRes['data'])) {
            system('kill -9 ' . implode(' ', $execRes['data']));
        }

        $commandTip = 'echo -e "\e[1;36m kill ' . SY_MODULE . ' zombies: \e[0m \e[1;32m \t[success] \e[0m"';
        system($commandTip);
    }

    /**
     * 获取服务启动状态
     */
    public function getStartStatus()
    {
        $fileContent = file_get_contents($this->_tipFile);
        $command = 'echo -e "\e[1;31m ' . SY_MODULE . ' start status fail \e[0m"';
        if (is_string($fileContent)) {
            if (strlen($fileContent) > 0) {
                $command = 'echo -e "' . $fileContent . '"';
            }
            file_put_contents($this->_tipFile, '');
        }
        system($command);
        exit();
    }

    /**
     * 启动工作进程
     * @param \swoole_server $server
     * @param int $workerId 进程编号
     */
    public function onWorkerStart(\swoole_server $server, $workerId)
    {
        //设置错误和异常处理
        set_exception_handler('\SyError\ErrorHandler::handleException');
        set_error_handler('\SyError\ErrorHandler::handleError');
        //设置时区
        date_default_timezone_set('PRC');
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);

        $this->_app = new Application(APP_PATH . '/conf/application.ini', SY_ENV);
        $this->_app->bootstrap()->getDispatcher()->returnResponse(true);
        $this->_app->bootstrap()->getDispatcher()->autoRender(false);

        if ($workerId >= $server->setting['worker_num']) {
            @cli_set_process_title(Server::PROCESS_TYPE_TASK . SY_MODULE . $this->_port);
        } else {
            @cli_set_process_title(Server::PROCESS_TYPE_WORKER . SY_MODULE . $this->_port);
        }
    }

    /**
     * 启动管理进程
     * @param \swoole_server $server
     */
    public function onManagerStart(\swoole_server $server)
    {
        @cli_set_process_title(Server::PROCESS_TYPE_MANAGER . SY_MODULE . $this->_port);
    }

    /**
     * 关闭服务
     * @param \swoole_server $server
     */
    public function onShutdown(\swoole_server $server)
    {
    }

    /**
     * 关闭连接
     * @param \swoole_server $server
     * @param int $fd 连接的文件描述符
     * @param int $reactorId reactor线程ID
     */
    public function onClose(\swoole_server $server, int $fd, int $reactorId)
    {
    }

    /**
     * 工作进程退出
     * @param \swoole_server $server
     * @param int $workerId 工作进程ID
     */
    public function onWorkerExit(\swoole_server $server, int $workerId)
    {
        $fdList = $server->connections;
        foreach ($fdList as $eFd) {
            if ($server->exist($eFd)) {
                $server->close($eFd);
            }
        }

        if (version_compare(SWOOLE_VERSION, '4.4.0', '>=')) {
            \swoole_timer::clearAll();
        }
    }

    /**
     * 启动主进程服务
     * @param \swoole_server $server
     */
    abstract public function onStart(\swoole_server $server);
    /**
     * 退出工作进程
     * @param \swoole_server $server
     * @param int $workerId
     * @return mixed
     */
    abstract public function onWorkerStop(\swoole_server $server, int $workerId);
    /**
     * 工作进程错误处理
     * @param \swoole_server $server
     * @param int $workId 进程编号
     * @param int $workPid 进程ID
     * @param int $exitCode 退出状态码
     */
    abstract public function onWorkerError(\swoole_server $server, $workId, $workPid, $exitCode);

    /**
     * 检测请求URI
     * @param string $uri
     * @return array
     */
    protected function checkRequestUri(string $uri) : array
    {
        $nowUri = $uri;
        $checkRes = [
            'uri' => '',
            'error' => '',
        ];

        $uriRes = Tool::handleYafUri($nowUri);
        if (strlen($uriRes) == 0) {
            $checkRes['uri'] = $nowUri;
        } else {
            $errRes = new Result();
            $errRes->setCodeMsg(ErrorCode::COMMON_ROUTE_URI_FORMAT_ERROR, $uriRes);
            $checkRes['error'] = $errRes->getJson();
            unset($errRes);
        }

        return $checkRes;
    }

    /**
     * 获取预处理函数
     * @param string $uri
     * @param array $frameMap
     * @param array $projectMap
     * @return bool|string
     */
    protected function getPreProcessFunction(string $uri, array $frameMap, array $projectMap)
    {
        $funcName = '';
        if (strlen($uri) == 5) {
            if (isset($frameMap[$uri])) {
                $funcName = $frameMap[$uri];
                if (strpos($funcName, 'preProcessFrame') !== 0) {
                    $funcName = false;
                }
            } elseif (isset($projectMap[$uri])) {
                $funcName = $projectMap[$uri];
                if (strpos($funcName, 'preProcessProject') !== 0) {
                    $funcName = false;
                }
            }
        }

        return $funcName;
    }

    protected function baseStart(array $registerMap)
    {
        $this->_server->set($this->_configs['swoole']);
        //绑定注册方法
        foreach ($registerMap as $eventName => $funcName) {
            $this->_server->on($eventName, [$this, $funcName]);
        }

        file_put_contents($this->_tipFile, '\e[1;36m start ' . SY_MODULE . ': \e[0m \e[1;31m \t[fail] \e[0m');
        $this->_server->start();
    }

    /**
     * @param \swoole_server $server
     */
    protected function basicStart(\swoole_server $server)
    {
        @cli_set_process_title(Server::PROCESS_TYPE_MAIN . SY_MODULE . $this->_port);

        Dir::create(SY_ROOT . '/pidfile/');
        if (file_put_contents($this->_pidFile, $server->master_pid) === false) {
            Log::error('write ' . SY_MODULE . ' pid file error');
        }

        file_put_contents($this->_tipFile, '\e[1;36m start ' . SY_MODULE . ': \e[0m \e[1;32m \t[success] \e[0m');

        $config = Tool::getConfig('project.' . SY_ENV . SY_PROJECT);
        Dir::create($config['dir']['store']['image']);
        Dir::create($config['dir']['store']['music']);
        Dir::create($config['dir']['store']['resources']);
        Dir::create($config['dir']['store']['cache']);

        self::$_syServer->set(self::$_serverToken, [
            'memory_usage' => memory_get_usage(),
            'timer_time' => 0,
            'request_times' => 0,
            'request_handling' => 0,
            'host_local' => $this->_host,
            'storepath_image' => $config['dir']['store']['image'],
            'storepath_music' => $config['dir']['store']['music'],
            'storepath_resources' => $config['dir']['store']['resources'],
            'storepath_cache' => $config['dir']['store']['cache'],
            'token_etime' => time() + 7200,
            'unique_num' => 100000000,
        ]);
    }

    /**
     * @param \swoole_server $server
     * @param int $workId
     */
    protected function basicWorkStop(\swoole_server $server, int $workId)
    {
        $errCode = $server->getLastError();
        if ($errCode > 0) {
            Log::error('swoole work stop,workId=' . $workId . ',errorCode=' . $errCode . ',errorMsg=' . print_r(error_get_last(), true));
        }
    }

    /**
     * @param \swoole_server $server
     * @param int $workId
     * @param int $workPid
     * @param int $exitCode
     */
    protected function basicWorkError(\swoole_server $server, $workId, $workPid, $exitCode)
    {
        if ($exitCode > 0) {
            $msg = 'swoole work error. work_id=' . $workId . '|work_pid=' . $workPid . '|exit_code=' . $exitCode . '|err_msg=' . $server->getLastError();
            Log::error($msg);
        }
    }

    /**
     * @return string
     */
    public static function getReqId() : string
    {
        if (isset($_SERVER['SYREQ_ID'])) {
            return $_SERVER['SYREQ_ID'];
        }
        $reqId = hash('md4', Tool::getNowTime() . Tool::createNonceStr(8));
        $_SERVER['SYREQ_ID'] = $reqId;
        return $reqId;
    }

    protected function handleReqExceptionByFrame(\Exception $e)
    {
        $error = new Result();
        if (is_numeric($e->getCode())) {
            $error->setCodeMsg((int)$e->getCode(), $e->getMessage());
        } else {
            $error->setCodeMsg(ErrorCode::COMMON_SERVER_ERROR, '服务出错');
        }

        return $error;
    }
}
