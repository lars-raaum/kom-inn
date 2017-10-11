<?php

$sms = file_exists(RESOURCE_PATH . '/sms.php') ? require RESOURCE_PATH . '/sms.php' : [];
$emails = file_exists(RESOURCE_PATH . '/emails.php') ? require RESOURCE_PATH . '/emails.php' : [];
$connections = require RESOURCE_PATH . '/connections-dist.php';

return [
    'dev' => [
        'base_url' => 'http://localhost:8000',
        'logfile' => RESOURCE_PATH . '/kom-inn-backend.log',
        'sms' => $sms + [
            'enabled' => false,
            'prefix' => 'DEV: '
        ],
        'emails' => $emails + [
            'enabled' => false,
            'prefix' => 'DEV: ',
            'salt' => 'kioslo',
        ],
        'connections' => $connections + [
            'driver'    => 'pdo_mysql',
            'host'      => '127.0.0.1',
            'port'      => '3306',
            'dbname'    => 'kominn',
            'user'      => 'root',
            'password'  => 'sa'
        ]
    ],
    'pre' => [
        'base_url' => 'https://dev.kom-inn.org',
        'logfile' => '/var/log/kom-inn/backend.log',
        'sms' => $sms + [
            'enabled' => false,
            'prefix' => 'PRE: '
        ],
        'emails' => $emails + [
            'enabled' => false,
            'prefix' => 'PRE: ',
        ],
        'connections' => $connections + [
            'driver'    => 'pdo_mysql',
            'host'      => 'localhost',
            'dbname'    => 'kominn',
            'charset'   => 'utf8mb4',
            'user'      => 'root',
            'password'  => 'sa'
        ]

    ],
    'pro' => [
        'base_url' => 'https://pro.kom-inn.org',
        'logfile' => '/var/log/kom-inn/backend.log',
        'sms' => $sms + [
            'enabled' => false,
            'prefix' => ''
        ],
        'emails' => $emails + [
            'enabled' => false,
            'prefix' => '',
        ],
        'connections' => $connections + [
            'driver'    => 'pdo_mysql',
            'host'      => 'localhost',
            'dbname'    => 'kominn',
            'user'      => 'root',
            'password'  => 'sa',
            'charset'   => 'utf8mb4'
        ]
    ]
];
