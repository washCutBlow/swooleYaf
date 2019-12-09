<?php

namespace Helper;

use SyServer\HttpServer;
use SyServer\RpcServer;
use Tool\Tool;
use SyTrait\SimpleTrait;

class ServiceRunner
{
    use SimpleTrait;

    /**
     * @param string $apiName api模块名称
     * @param array $totalModule 包含所有模块的数组
     */
    public static function run(string $apiName, array $totalModule)
    {
        /*eg:sudo php -c /etc/php-cli.ini /Users/zhanglei061/src/kaiyuanzoudu/sydemo_project/helper_service.php -n sy_api -s start -module a01api -port 7000*/
        /*sudo php -c /etc/php-cli.ini /Users/zhanglei061/src/kaiyuanzoudu/sydemo_project/helper_service.php -n sy_user -s start -module a01user -port 7010*/

        /* sudo php -c /etc/php-cli.ini /Users/zhanglei061/src/kaiyuanzoudu/sydemo_project/helper_service.php -n sy_api -s start -module a01api -port 7000 && sudo php -c /etc/php-cli.ini       /Users/zhanglei061/src/kaiyuanzoudu/sydemo_project/helper_service.php -n sy_api -s startstatus -module a01api -port 7000
         * sudo php -c /etc/php-cli.ini /Users/zhanglei061/src/kaiyuanzoudu/sydemo_project/helper_service.php -n sy_user -s start -module a01user -port 7010 && sudo php -c /etc/php-cli.ini /Users/zhanglei061/src/kaiyuanzoudu/sydemo_project/helper_service.php -n sy_user -s startstatus -module a01user -port 7010
         *
         *
         * */
        /*a01api
        array(2) {
          [0] =>
          string(6) "a01api"
          [1] =>
          string(7) "a01user"
        }
        */
        // sy_api
        $projectName = trim(Tool::getClientOption('-n', false, ''));
        if (strlen($projectName) == 0) {
            exit('参数 -n 服务项目名称无效,必须与项目目录相同,否则无法加载 profile文件' . PHP_EOL);
        }

        $projectPath = SY_ROOT . '/' . $projectName;
        if (!is_dir($projectPath)) {
            exit($projectName . ' dir not exist' . PHP_EOL);
        }
        define('APP_PATH', $projectPath);

        $moduleName = trim(Tool::getClientOption('-module', false, ''));
        if (strlen($moduleName) == 0) {
            exit('module name must exist' . PHP_EOL);
        } elseif (!in_array($moduleName, $totalModule, true)) {
            exit('module name error' . PHP_EOL);
        }
        define('SY_MODULE', $moduleName);

        $port = trim(Tool::getClientOption('-port', false, ''));
        if (!ctype_digit($port)) {
            exit('port must exist and is integer' . PHP_EOL);
        }
        $truePort = (int)$port;

        if ($moduleName == $apiName) {
            $server = new HttpServer($truePort);
        } else {
            $server = new RpcServer($truePort);
        }

        $action = Tool::getClientOption('-s', false, 'start');
        switch ($action) {
            case 'start':
                $server->start();
                break;
            case 'stop':
                $server->stop();
                break;
            case 'restart':
                $server->stop();
                sleep(3);
                $server->start();
                break;
            case 'kz':
                $server->killZombies();
                break;
            case 'startstatus':
                $server->getStartStatus();
                break;
            default:
                $server->help();
        }
    }
}
