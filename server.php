<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2019/9/4 0004
 * Time: 19:55
 */
$http = new swoole_http_server("0.0.0.0", 8800);
$http->on('request', function ($request, $response) {
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Hello Swoole. #' . rand(1000, 9999) . '</h1>');
});
$http->start();