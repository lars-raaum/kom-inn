<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
    $_SERVER['PHP_AUTH_USER'] = $_SERVER['PHP_AUTH_USER'] ?? "NONE";
});
require_once __DIR__.'/../../resources/configuration.php';

$dtt = \Doctrine\DBAL\Types\Type::getType('datetime');
$types = ['updated' => $dtt, 'created' => $dtt];

require_once __DIR__.'/../controllers/matches.php';
require_once __DIR__.'/../controllers/hosts.php';
require_once __DIR__.'/../controllers/guests.php';
require_once __DIR__.'/../controllers/people.php';
require_once __DIR__.'/../controllers/importcsv.php';

$app->run();