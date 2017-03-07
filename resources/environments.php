<?php

$sms = file_exists(RESOURCE_PATH . '/sms.php') ? require RESOURCE_PATH . '/sms.php' : [];
$emails = file_exists(RESOURCE_PATH . '/emails.php') ? require RESOURCE_PATH . '/emails.php' : [];
$connections = file_exists(RESOURCE_PATH . '/connections.php') ? require RESOURCE_PATH . '/connections.php' : [];

return [
    'dev' => [
        'base_url' => 'http://localhost:8000',
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
            'host'      => 'localhost',
            'dbname'    => 'kominn',
            'user'      => 'root',
            'password'  => '',
            'charset'   => 'utf8mb4'
        ]
    ],
    'pre' => [
        'base_url' => 'https://dev.kom-inn.org',
        'sms' => $sms + [
            'enabled' => true,
            'prefix' => 'PRE: '
        ],
        'emails' => $emails + [
            'enabled' => true,
            'prefix' => 'PRE: ',
        ],
        'connections' => $connections + [
            'driver'    => 'pdo_mysql',
            'host'      => 'localhost',
            'dbname'    => 'kominn',
            'charset'   => 'utf8mb4'
        ]

    ],
    'pro' => [
        'base_url' => 'https://kom-inn.org',
        'sms' => $sms + [
            'enabled' => true,
            'prefix' => ''
        ],
        'emails' => $emails + [
            'enabled' => true,
            'prefix' => '',
        ],
        'connections' => $connections + [
            'driver'    => 'pdo_mysql',
            'host'      => 'localhost',
            'dbname'    => 'kominn',
            'charset'   => 'utf8mb4'
        ]
    ]
];
