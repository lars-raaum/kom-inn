<?php

$app['debug'] = true;

$connection = require_once 'connections.php';
$app->register(new Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => $connection
]);
