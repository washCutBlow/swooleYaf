<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2019/9/6 0006
 * Time: 11:55
 */
namespace Tool;

use SyConstant\Server;
use SyTrait\SimpleTrait;

class Tool
{
    use SimpleTrait;

    private static $totalChars = [
        '2', '3', '4', '5', '6', '7', '8', '9',
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
        'i', 'j', 'k', 'm', 'n', 'p', 'q', 'r',
        's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
        'I', 'J', 'K', 'L', 'M', 'N', 'P', 'Q',
        'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y',
        'Z',
    ];
    private static $lowerChars = [
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
        'i', 'j', 'k', 'm', 'n', 'p', 'q', 'r',
        's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
    ];
    private static $numLowerChars = [
        '2', '3', '4', '5', '6', '7', '8', '9',
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
        'i', 'j', 'k', 'm', 'n', 'p', 'q', 'r',
        's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
    ];

    /**
     * 获取命令行输入
     * @param string|int $key 键名
     * @param bool $isIndexKey 键名是否为索引 true:是索引 false:不是索引
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getClientOption($key, bool $isIndexKey = false, $default = null)
    {
        global $argv;

        $option = $default;
        if ($isIndexKey) {
            if (isset($argv[$key])) {
                $option = $argv[$key];
            }
        } else {
            foreach ($argv as $eKey => $eVal) {
                if (($key == $eVal) && isset($argv[$eKey + 1])) {
                    $option = $argv[$eKey + 1];
                    break;
                }
            }
        }

        return $option;
    }

    /**
     * 获取数组值
     * @param array $array 数组
     * @param string|int $key 键值
     * @param object $default 默认值
     * @param bool $isRecursion 是否递归查找,false:不递归 true:递归
     * @return mixed
     */
    public static function getArrayVal(array $array, $key, $default = null, bool $isRecursion = false)
    {
        if (!$isRecursion) {
            return $array[$key] ?? $default;
        }

        $keyArr = explode('.', (string)$key);
        $tempData = $array;
        unset($array);
        foreach ($keyArr as $eKey) {
            if (is_array($tempData) && isset($tempData[$eKey])) {
                $tempData = $tempData[$eKey];
            } else {
                return $default;
            }
        }

        return $tempData;
    }

    /**
     * 获取配置信息
     * @param string $tag 配置标识
     * @param string $field 字段名称
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getConfig(string $tag, string $field = '', $default = null)
    {
        $configs = \Yaconf::get($tag);
        if (is_null($configs)) {
            return $default;
        } elseif (is_array($configs) && (strlen($field) > 0)) {
            return self::getArrayVal($configs, $field, $default);
        } else {
            return $configs;
        }
    }

    /**
     * 执行系统命令
     * @param string $command
     * @return array
     */
    public static function execSystemCommand(string $command) : array
    {
        $trueCommand = trim($command);
        if (strlen($trueCommand) == 0) {
            return [
                'code' => 9999,
                'msg' => '执行命令不能为空',
            ];
        }

        $code = 0;
        $output = [];
        $msg = exec($trueCommand, $output, $code);
        if ($code == 0) {
            return [
                'code' => 0,
                'data' => $output,
            ];
        } else {
            return [
                'code' => $code,
                'msg' => $msg,
            ];
        }
    }

    /**
     * 获取当前时间戳
     * @return int
     */
    public static function getNowTime()
    {
        return $_SERVER[Server::SERVER_DATA_KEY_TIMESTAMP] ?? time();
    }

    /**
     * 生成随机字符串
     * @param int $length 需要获取的随机字符串长度
     * @param string $dataType 数据类型
     *   total: 数字,大小写字母
     *   lower: 小写字母
     *   numlower: 数字,小写字母
     * @return string
     */
    public static function createNonceStr(int $length, string $dataType = 'total') : string
    {
        $resStr = '';
        switch ($dataType) {
            case 'lower':
                for ($i = 0; $i < $length; $i ++) {
                    $resStr .= self::$lowerChars[random_int(0, 23)];
                }
                break;
            case 'numlower':
                for ($i = 0; $i < $length; $i ++) {
                    $resStr .= self::$numLowerChars[random_int(0, 31)];
                }
                break;
            default:
                for ($i = 0; $i < $length; $i ++) {
                    $resStr .= self::$totalChars[random_int(0, 56)];
                }
        }

        return $resStr;
    }
}