<?php

$app['debug'] = true;

$app->register(new Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => [
        'driver'    => 'pdo_mysql',
        'host'      => 'localhost',
        'dbname'    => 'kominn',
        'user'      => 'root',
        'password'  => 'root',
        'charset'   => 'utf8mb4'
	]
]);