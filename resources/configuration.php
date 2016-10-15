<?php

$app['debug'] = false;

$app->register(new Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => [
        'driver'    => 'pdo_mysql',
        'host'      => 'spp.dev',
        'dbname'    => 'kominn',
        'user'      => 'root',
        'password'  => 'root',
        'charset'   => 'utf8mb4'
	]
]);