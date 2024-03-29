<?php
final class SyFrameLoader {
    /**
     * @var \SyFrameLoader
     */
    private static $instance = null;
    /**
     * @var array
     */
    private $preHandleMap = [];
    /**
     * excel未初始化标识 true：未初始化 false：已初始化
     * @var bool
     */
    private $excelStatus = true;

    private function __construct() {
        $this->preHandleMap = [
            'PHPExcel' => 'preHandlePhpExcel',
        ];
    }

    private function __clone()
    {
    }

    /**
     * @return \SyFrameLoader
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 加载文件
     * @param string $className 类名
     * @return bool
     */
    public function loadFile(string $className) : bool
    {
        $nameArr = explode('/', $className);
        $funcName = $this->preHandleMap[$nameArr[0]] ?? null;
        if (is_null($funcName)) {
            $nameArr = explode('_', $className);
            $funcName = $this->preHandleMap[$nameArr[0]] ?? null;
        }

        $file = is_null($funcName) ? SY_FRAME_LIBS_ROOT . $className . '.php' : $this->$funcName($className);
        if (is_file($file) && is_readable($file)) {
            require_once $file;
            return true;
        }

        return false;
    }

    private function preHandlePhpExcel(string $className) : string
    {
        if ($this->excelStatus) {
            define('PHPEXCEL_ROOT', SY_FRAME_LIBS_ROOT . 'Excel/');
            $this->excelStatus = false;
        }

        return SY_FRAME_LIBS_ROOT . 'Excel/' . str_replace('_', '/', $className) . '.php';
    }
}

final class SyProjectLoader
{
    /**
     * @var \SyProjectLoader
     */
    private static $instance = null;

    private function __construct()
    {
    }

    /**
     * @return \SyProjectLoader
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 加载文件
     * @param string $className 类名
     * @return bool
     */
    public function loadFile(string $className) : bool
    {
        $file = SY_PROJECT_LIBS_ROOT . $className . '.php';
        if (is_file($file) && is_readable($file)) {
            require_once $file;
            return true;
        }

        return false;
    }
}

/**
 * 基础公共类自动加载
 * @param string $className 类全名
 * @return bool
 */
function syFrameAutoload(string $className)
{
    $trueName = str_replace([
        '\\',
        "\0",
    ], [
        '/',
        '',
    ], $className);
    return SyFrameLoader::getInstance()->loadFile($trueName);
}

/**
 * 项目公共类自动加载
 * @param string $className 类全名
 * @return bool
 */
function syProjectAutoload(string $className)
{
    $trueName = str_replace([
        '\\',
        "\0",
    ], [
        '/',
        '',
    ], $className);
    return SyProjectLoader::getInstance()->loadFile($trueName);
}

spl_autoload_register('syFrameAutoload');
spl_autoload_register('syProjectAutoload');

require_once __DIR__ . '/helper_defines.php';