<?php

$app['debug'] = true;

$app->register(new Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => [
        'driver'    => 'pdo_mysql',
        'host'      => '127.0.0.1',
        'dbname'    => 'kominn',
        'user'      => 'kominn',
        'password'  => '',
        'charset'   => 'utf8mb4'
	]
]);
