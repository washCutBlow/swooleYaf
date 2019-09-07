<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2019/9/6 0006
 * Time: 11:58
 */
namespace SyConstant;

use SyTrait\SimpleTrait;

class ProjectBase
{
    use SimpleTrait;

    //服务预处理常量,标识长度为5位,第一位固定为/,后四位代表不同预处理操作,其中后四位全为数字的为框架内部预留标识
    const PRE_PROCESS_TAG_HTTP_FRAME_SERVER_INFO = '/0000'; //HTTP服务框架内部标识-服务信息
    const PRE_PROCESS_TAG_HTTP_FRAME_PHP_INFO = '/0001'; //HTTP服务框架内部标识-php环境信息
    const PRE_PROCESS_TAG_HTTP_FRAME_HEALTH_CHECK = '/0002'; //HTTP服务框架内部标识-健康检测
    const PRE_PROCESS_TAG_HTTP_FRAME_REFRESH_TOKEN_EXPIRE = '/0003'; //HTTP服务框架内部标识-更新令牌过期时间
    const PRE_PROCESS_TAG_RPC_FRAME_SERVER_INFO = '/0000'; //RPC服务框架内部标识-服务信息

    //容量常量
    const SIZE_SERVER_PACKAGE_MAX = 6291456; //服务端容量-最大接收数据大小,单位为字节,默认为6M
    const SIZE_CLIENT_SOCKET_BUFFER = 12582912; //客户端容量-连接的缓存区大小,单位为字节,默认为12M
    const SIZE_CLIENT_BUFFER_OUTPUT = 4194304; //客户端容量-单次最大发送数据大小,单位为字节,默认为4M
}
