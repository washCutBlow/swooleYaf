<?php
require_once __DIR__ . '/helper_load.php';

$projects = include __DIR__ . '/config_projects.php';
//$commandPrefix = 'sudo /usr/local/php7/bin/php ' . __DIR__ . '/helper_service.php';
//  php helper_service_manager.php -s start-all
$commandPrefix = 'sudo php -c /etc/php-cli.ini ' . __DIR__ . '/helper_service.php';
/*echo $commandPrefix;
var_dump($projects);
exit(1);*/
\Helper\ServiceManager::handleAllService($commandPrefix, $projects);
