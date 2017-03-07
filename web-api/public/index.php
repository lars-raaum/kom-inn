<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : []);
    }
});
require_once __DIR__.'/../../resources/configuration.php';

$email_config = require_once RESOURCE_PATH . '/emails.php';
$app->register(new app\Mailer($email_config));
$sms_config = require_once RESOURCE_PATH . '/sms.php';
$app->register(new app\Sms($sms_config));

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
        return $app->json(null, $e->getCode(), ['X-Error-Message' => $e->getMessage()]);
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
        error_log($e->getMessage());
        return $app->json(compact('message', 'code'));
    }
});

$app->run();

