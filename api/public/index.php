<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;
// ... definitions

$app->get('/guests', function ()  {
    $output = [
    	[
    		"id" => 1,
    		"name" => "Ola Dunk"
    	]
    ];


    return json_encode($output);
});

$app->run();