<?php

$app['debug'] = true;

define('RESOURCE_PATH', realpath(__DIR__));

$connection = require_once 'connections.php';
$app->register(new Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => $connection
]);

