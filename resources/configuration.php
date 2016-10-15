<?php

$app['debug'] = true;

$app->register(new Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => [
        'driver'    => 'pdo_mysql',
        'host'      => 'docker.default',
        'dbname'    => 'kominn',
        'user'      => 'root',
        'password'  => '',
        'charset'   => 'utf8mb4'
	]
]);