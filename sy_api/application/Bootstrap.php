<?php
/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends \Yaf\Bootstrap_Abstract
{
    private function __clone()
    {
    }

    /**
     * APP配置数组
     * @var array
     */
    private static $appConfigs = [];
    /**
     * 允许的模块列表
     * @var array
     */
    private static $acceptModules = [];
    /**
     * 首次请求标识,true:首次请求 false:非首次请求
     * @var bool
     */
    protected static $firstTag = true;

    public function _initBoot(\Yaf\Dispatcher $dispatcher)
    {
        if (self::$firstTag) {
            //设置应用配置
            $config = \Yaf\Application::app()->getConfig();
            self::$appConfigs = $config->toArray();
            if (empty(self::$appConfigs)) {
                throw new \SyException\Swoole\ServerException('APP配置不能为空', \SyConstant\ErrorCode::SWOOLE_SERVER_PARAM_ERROR);
            }
            if (isset(self::$appConfigs['application']['modules'])) {
                $moduleArr = explode(',', self::$appConfigs['application']['modules']);
                foreach ($moduleArr as $eModule) {
                    $eModuleTag = trim($eModule);
                    if (ctype_alnum($eModuleTag) && ctype_alpha($eModuleTag{0})) {
                        self::$acceptModules[$eModuleTag] = 1;
                    }
                }
            }
            if (empty(self::$acceptModules)) {
                throw new \SyException\Swoole\ServerException('允许的模块列表不能为空', \SyConstant\ErrorCode::SWOOLE_SERVER_PARAM_ERROR);
            }
            \Yaf\Registry::set('config', $config);
            //设置默认模块
            $defaultModule = isset(self::$appConfigs['application']['dispatcher']['defaultModule']) ? self::$appConfigs['application']['dispatcher']['defaultModule'] : '';
            if (strlen($defaultModule) == 0) {
                throw new \SyException\Swoole\ServerException('默认模块名不存在', \SyConstant\ErrorCode::SWOOLE_SERVER_PARAM_ERROR);
            } elseif (!ctype_alnum($defaultModule)) {
                throw new \SyException\Swoole\ServerException('默认模块名不合法', \SyConstant\ErrorCode::SWOOLE_SERVER_PARAM_ERROR);
            } elseif (!ctype_alpha($defaultModule{0})) {
                throw new \SyException\Swoole\ServerException('默认模块名不合法', \SyConstant\ErrorCode::SWOOLE_SERVER_PARAM_ERROR);
            }
            define('SY_DEFAULT_MODULE', ucfirst($defaultModule));

            //设置默认控制器
            $defaultController = isset(self::$appConfigs['application']['dispatcher']['defaultController']) ? self::$appConfigs['application']['dispatcher']['defaultController'] : '';
            if (strlen($defaultController) == 0) {
                throw new \SyException\Swoole\ServerException('默认控制器名不存在', \SyConstant\ErrorCode::SWOOLE_SERVER_PARAM_ERROR);
            } elseif (!ctype_alnum($defaultController)) {
                throw new \SyException\Swoole\ServerException('默认控制器名不合法', \SyConstant\ErrorCode::SWOOLE_SERVER_PARAM_ERROR);
            } elseif (!ctype_alpha($defaultController{0})) {
                throw new \SyException\Swoole\ServerException('默认控制器名不合法', \SyConstant\ErrorCode::SWOOLE_SERVER_PARAM_ERROR);
            }
            define('SY_DEFAULT_CONTROLLER', ucfirst($defaultController));

            //设置默认方法
            $defaultAction = isset(self::$appConfigs['application']['dispatcher']['defaultAction']) ? self::$appConfigs['application']['dispatcher']['defaultAction'] : '';
            if (strlen($defaultAction) == 0) {
                throw new \SyException\Swoole\ServerException('默认方法名不存在', \SyConstant\ErrorCode::SWOOLE_SERVER_PARAM_ERROR);
            } elseif (!ctype_alnum($defaultAction)) {
                throw new \SyException\Swoole\ServerException('默认方法名不合法', \SyConstant\ErrorCode::SWOOLE_SERVER_PARAM_ERROR);
            } elseif (!ctype_alpha($defaultAction{0})) {
                throw new \SyException\Swoole\ServerException('默认方法名不合法', \SyConstant\ErrorCode::SWOOLE_SERVER_PARAM_ERROR);
            }
            define('SY_DEFAULT_ACTION', lcfirst($defaultAction));

            $dispatcher->setDefaultModule(SY_DEFAULT_MODULE)
                       ->setDefaultController(SY_DEFAULT_CONTROLLER)
                       ->setDefaultAction(SY_DEFAULT_ACTION);
            self::$firstTag = false;
        }
    }
}
