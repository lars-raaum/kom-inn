<?php

require_once __DIR__ . '/vendor/autoload.php';

$options = app\Cli::get_console_commands();
if ($options['help']) {
    echo PHP_EOL.
    "Usage:".
    PHP_EOL . PHP_EOL .
    "  $ php emails.php [-(v|h|d)] [--(verbose|help|dry)] [-(m)=<value>] [--(max) <value>]" .
    PHP_EOL . PHP_EOL . PHP_EOL .
    "  h | help : this message" . PHP_EOL .
    "  v | verbose : output information about each mail sent" . PHP_EOL .
    "  d | dry : output info on what would have been done, but send nothing" . PHP_EOL .
    "  m | max : limits the amount of mails to send to this number passed as value" . PHP_EOL;
    die();
}
define('RESOURCE_PATH', __DIR__ . '/../resources');

$app = new app\Cli($options);

$connection = require_once RESOURCE_PATH . '/connections.php';
$app->register(new Silex\Provider\DoctrineServiceProvider(), ['db.options' => $connection]);

$email_config = require_once RESOURCE_PATH . '/emails.php';
$app->register(new app\Emailing($email_config));

$app->register(new app\models\Hosts());
$app['PHP_AUTH_USER'] = __FILE__;

// @TODO refactor to pattern similar to controllers for the api app?
$app->run(function(\app\Cli $app) {

    if (isset($app['max'])) {
        $limit = (int) $app['max'];
    }
    if (isset($limit)) {
        $sql = "SELECT * FROM matches WHERE status = 0 AND created < DATE_ADD(CURDATE(), INTERVAL - 10 DAY) ORDER BY id DESC LIMIT {$limit}";
    } else {
        $sql = "SELECT * FROM matches WHERE status = 0 AND created < DATE_ADD(CURDATE(), INTERVAL - 10 DAY) ORDER BY id DESC";
    }
    $app->verbose("SQL [ $sql ] - by [CRON]");
    $matches = $app['db']->fetchAll($sql);

    foreach ($matches as $match) {
        $match['host'] = $app['hosts']->get($match['host_id']);

        $e = $match['host']['email'];
        if ($app['dry']) {
            $app->out("Sending nagging mail to host: [$e]");
        } else {
            $result = $app['email']->sendNaggingMail($match);
            if ($result) {
                $app->verbose("Mail sent to [$e]");
            } else {
                $app->error("ERROR! Failed to send mail to [$e]");
            }
        }
    }

});
