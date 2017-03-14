<?php

require_once __DIR__.'/../vendor/autoload.php';
define('RESOURCE_PATH', realpath(__DIR__.'/../../resources'));

$app = new Silex\Application();

use Symfony\Component\HttpFoundation\Request;

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : []);
    }
});

$app->register(new Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => \app\Environment::get('connections')
]);
$app->register(new Silex\Provider\MonologServiceProvider(), [
    'monolog.logfile' => \app\Environment::get('logfile')
]);

$app->register(new app\Mailer());
$app->register(new app\Sms());

$app->register(new \app\models\People());
$app->register(new \app\models\Guests());
$app->register(new \app\models\Hosts());
$app->register(new \app\models\Matches());

$app['PHP_AUTH_USER'] = 'PUBLIC';

require_once __DIR__.'/../controllers/register.php';
require_once __DIR__.'/../controllers/feedback.php';

$app->error(function (\Exception $e) use ($app) {
    if ($e instanceof app\exceptions\ApiException) {
        return $app->json(null, $e->getCode(), ['X-Error-Message' => $e->getMessage()]);
    } elseif ($e instanceof app\Exception) {
        error_log("ERROR: {$e->getCode()} : {$e->getMessage()}");
        return $app->json(null, 500, ['X-Error-Message' => $e->getMessage()]);
    } else {
        $code = $e->getCode();
        switch ($code) {
            case 404:
                $message = 'The requested page could not be found.';
                break;
            default:
                $code = 500;
                $message = 'We are sorry, but something went terribly wrong.';
        }
        error_log($e->getCode() . " : " . $e->getMessage());
        return $app->json(null, $code, ['X-Error-Message' => $message]);
    }
});

$app->run();

