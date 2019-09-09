<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2019/9/6 0006
 * Time: 13:56
 */
namespace SyConstant;

use SyTrait\SimpleTrait;

final class Server
{
    use SimpleTrait;

    const SERVER_TYPE_API_GATE = 'api'; //服务端类型-api入口
    const SERVER_TYPE_API_MODULE = 'rpc'; //服务端类型-api模块
    const SERVER_DATA_KEY_TIMESTAMP = 'SYREQ_TIME'; //服务端内部数据键名-请求时间戳

    //进程常量
    const PROCESS_TYPE_TASK = 'Task'; //类型-task
    const PROCESS_TYPE_WORKER = 'Worker'; //类型-worker
    const PROCESS_TYPE_MANAGER = 'Manager'; //类型-manager
    const PROCESS_TYPE_MAIN = 'Main'; //类型-main

    //环境常量
    public static $totalEnvProject = [
        self::ENV_PROJECT_DEV,
        self::ENV_PROJECT_PRODUCT,
    ];
    public static $totalEnvSystem = [
        self::ENV_SYSTEM_BSD,
        self::ENV_SYSTEM_MAC,
        self::ENV_SYSTEM_LINUX,
    ];
    const ENV_PROJECT_DEV = 'dev'; //项目环境-测试
    const ENV_PROJECT_PRODUCT = 'product'; //项目环境-生产
    const ENV_SYSTEM_BSD = 'FreeBSD'; //系统环境-bsd
    const ENV_SYSTEM_MAC = 'Darwin'; //系统环境-mac
    const ENV_SYSTEM_LINUX = 'Linux'; //系统环境-linux
    const ENV_PORT_MIN = 1024; //端口-最小
    const ENV_PORT_MAX = 65535; //端口-最大

    //版本常量
    const VERSION_MIN_PHP = '7.1.0'; //最低版本-php
    const VERSION_MIN_SWOOLE = '4.3.6'; //最低版本-swoole
    const VERSION_MIN_SEASLOG = '1.9.0'; //最低版本-seaslog
    const VERSION_MIN_YAC = '2.0.2'; //最低版本-yac
    const VERSION_MIN_YAF = '3.0.7'; //最低版本-yaf

    //注册常量
    const REGISTRY_NAME_SERVICE_ERROR = 'SERVICE_ERROR'; //名称-服务错误
    const REGISTRY_NAME_REQUEST_HEADER = 'REQUEST_HEADER'; //名称-请求头
    const REGISTRY_NAME_REQUEST_SERVER = 'REQUEST_SERVER'; //名称-服务器信息
    const REGISTRY_NAME_RESPONSE_HEADER = 'RESPONSE_HEADER'; //名称-响应头
    const REGISTRY_NAME_RESPONSE_COOKIE = 'RESPONSE_COOKIE'; //名称-响应cookie
    const REGISTRY_NAME_PREFIX_VALIDATOR = 'VALIDATOR_'; //名称前缀-校验器
    const REGISTRY_NAME_PREFIX_CONTROLLER = 'CONTROLLER_'; //名称前缀-控制器
    const REGISTRY_NAME_PREFIX_ASPECT_BEFORE = 'ASPECTBEFORE_'; //名称前缀-前置切面
    const REGISTRY_NAME_PREFIX_ASPECT_AFTER = 'ASPECTAFTER_'; //名称前缀-后置切面

    //路由常量
    const ROUTE_TYPE_SIMPLE = 'simple'; //类型-简单路由

    //熔断器常量
    const FUSE_STATE_OPEN = 'open'; //状态-开启
    const FUSE_STATE_CLOSED = 'closed'; //状态-关闭
    const FUSE_STATE_HALF_OPEN = 'half_open'; //状态-半开
    const FUSE_TIME_ERROR_STAT = 15; //错误统计间隔时间,单位为秒
    const FUSE_TIME_OPEN_KEEP = 10; //开启状态保持时间,单位为秒
    const FUSE_NUM_REQUEST_ERROR = 20; //请求出错次数
    const FUSE_NUM_HALF_REQUEST_SUCCESS = 10; //半开状态请求成功次数
    const FUSE_MSG_REQUEST_ERROR = '{"code":10001,"data":[],"msg":"服务繁忙,请稍后重试"}'; //请求出错提示消息
}
