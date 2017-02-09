<?php

require_once __DIR__ . '/vendor/autoload.php';

$options = app\Cli::get_console_commands();
if ($options['help']) {
    echo PHP_EOL;
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

$app = new app\Cli($options);

$connection = require_once __DIR__ . '/../resources/connections.php';

define('RESOURCE_PATH', realpath(__DIR__ . '/../resources'));

$app->register(new Silex\Provider\DoctrineServiceProvider(), [
    'db.options' => $connection
]);

// @TODO refactor to pattern similar to controllers for the api app?
$app->run(function($app) {

    if (isset($app['max'])) {
        $limit = (int) $app['max'];
    }
    if (isset($limit)) {
        $sql = "SELECT * FROM matches WHERE status = 0 AND created < DATE_ADD(CURDATE(), INTERVAL - 2 DAY) ORDER BY id DESC LIMIT {$limit}";
    } else {
        $sql = "SELECT * FROM matches WHERE status = 0 AND created < DATE_ADD(CURDATE(), INTERVAL - 2 DAY) ORDER BY id DESC";
    }
    $app->verbose("SQL [ $sql ] - by [CRON]");
    $matches = $app['db']->fetchAll($sql);
    $sender = new app\Emailing();

    foreach ($matches as $match) {
        $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
        $match['host'] = $app['db']->fetchAssoc($sql, [(int) $match['host_id']]);

        $e = $match['host']['email'];
        if ($app['dry']) {
            $app->out("Sending nagging mail to host: [$e]");
        } else {
            $result = $sender->sendNaggingMail($match);
            if ($result) {
                $app->verbose("Mail sent to [$e]");
            } else {
                $app->error("ERROR! Failed to send mail to [$e]");
            }
        }
    }

});
