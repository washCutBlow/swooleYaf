<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2017/8/22 0022
 * Time: 8:09
 */
namespace Request;

abstract class SyRequest
{
    public function __construct()
    {
    }

    /**
     * 获取请求参数
     * @param mixed $key 键名
     * @param mixed $default 默认值
     * @return array|mixed
     */
    public static function getParams(string $key = null, $default = null)
    {
        if ($key === null) {
            $val = array_merge($_GET, $_POST);
        } elseif (isset($_GET[$key])) {
            $val = $_GET[$key];
        } elseif (isset($_POST[$key])) {
            $val = $_POST[$key];
        } else {
            $val = $default;
        }

        return $val;
    }

    /**
     * 请求参数是否存在
     * @param string $key 键名
     * @return bool true:存在 false:不存在
     */
    public static function existParam(string $key)
    {
        if ($key === null) {
            return false;
        } elseif (isset($_GET[$key])) {
            return true;
        } elseif (isset($_POST[$key])) {
            return true;
        } else {
            return false;
        }
    }
}
