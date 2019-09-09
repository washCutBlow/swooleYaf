<?php
$serverHost = \Yaconf::get('syserver.base.server.host');

return [
    0 => [
        'module_type' => 'api',
        'module_path' => 'sy_api',
        'module_name' => 'a01api',
        'listens' => [
            0 => [
                'host' => $serverHost,
                'port' => 7000,
            ],
        ],
    ],
    1 => [
        'module_type' => 'rpc',
        'module_path' => 'sy_user',
        'module_name' => 'a01user',
        'listens' => [
            0 => [
                'host' => $serverHost,
                'port' => 7010,
            ],
        ],
    ],
];