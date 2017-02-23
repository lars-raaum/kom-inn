<?php

use app\mails\Reminders;

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
$app->register(new app\models\Matches());
$app->register(new app\models\People());
$app['PHP_AUTH_USER'] = __FILE__;

ini_set("error_log", "emails.log"); // Get the

// @TODO refactor to pattern similar to controllers for the api app?
$app->run(function(\app\Cli $app) {

    if (isset($app['max'])) {
        $limit = (int) $app['max'];
    }
    if (isset($limit)) {
        $sql = "SELECT *, DATEDIFF(NOW(), `created`) as `ago` FROM matches WHERE status = 0 AND created < DATE_ADD(CURDATE(), INTERVAL - 2 DAY) ORDER BY id DESC LIMIT {$limit}";
    } else {
        $sql = "SELECT *, DATEDIFF(NOW(), `created`) as `ago` FROM matches WHERE status = 0 AND created < DATE_ADD(CURDATE(), INTERVAL - 2 DAY) ORDER BY id DESC";
    }
    $app->verbose("SQL [ $sql ] - by [CRON]", "");
    $matches = $app['db']->fetchAll($sql);

    $total = count($matches);
    $app->verbose("Found {$total} active matches", "");

    $counter_keys = array_merge(Reminders::$TYPES, ["DELETE", "SKIP", "ERROR", "TOTAL"]);
    $fn = function($o, $v) { $o[$v] = 0; return $o; };
    $counters = array_reduce($counter_keys, $fn, []);

    /** @var \app\Emailing $mailer */
    $mailer = $app['email'];
    foreach ($matches as $match) {
        $match['host'] = $app['hosts']->get($match['host_id']);
        $app->verbose("Match {$match['id']}");
        /* Tasks:
            - Match created between 2 and 4 days ago, send first reminder mail
            - Match created between 4 and 7 days ago, send second reminder mail
            - Match created between 7 and 9 days ago, send third reminder mail
            - Match created more than 9 days ago and still unconfirmed, cancel match and delete host
        */
        try {
            $created = $match['ago'];
            if ($created < 2) { // It is new, do nothing
                $counters['SKIP']++;
            } elseif ($created > 9) {// Match is too old, lets cancel it
                if ($app['dry'] == false) {
                    $app['matches']->delete($match['id']);
                    $app['people']->delete($match['host_id']);
                    $app['people']->setToActive($match['guest_id']);
                }
                $app->verbose("Deleting host [{$match['host_id']}]");
                $app->verbose("Setting guest to active [{$match['guest_id']}]");
                $app->verbose("Cancelling match [{$match['id']}]");
                $counters['DELETE']++;
            } elseif ($created >= 2 && $created <= 3) {
                if ($app['dry'] == false) {
                    $mailer->sendReminderMail($match, Reminders::FIRST);
                }
                $app->verbose("First reminder sent to [{$match['host']['email']}]");
                $counters[Reminders::FIRST]++;
            } elseif ($created >= 4 && $created <= 6) {
                if ($app['dry'] == false) {
                    $mailer->sendReminderMail($match, Reminders::SECOND);
                }
                $app->verbose("Second reminder sent to [{$match['host']['email']}]");
                $counters[Reminders::SECOND]++;
            } elseif ($created >= 7 && $created <= 9) {
                if ($app['dry'] == false) {
                    $mailer->sendReminderMail($match, Reminders::THIRD);
                }
                $app->verbose("Third reminder sent to [{$match['host']['email']}]");
                $counters[Reminders::THIRD]++;
            }
        } catch (\app\Exception $e) {
            $app->error("ERROR! $e->getMessage()");
            $counters['ERROR']++;
        } catch (\Exception $e) {
            error_log("Failed to handle match {$match['id']} : " . $e->getMessage());
            $app->verbose(" ");
            $counters['ERROR']++;
            $counters['TOTAL']++;
            break;
        }
        $app->verbose(" ");
        $counters['TOTAL']++;

    }
    $app->verbose(" ", " Handled: " . $counters['TOTAL']);
    $app->verbose("  Skipped: " . $counters['SKIP']);
    $app->verbose("  Deleted: " . $counters['DELETE']);
    $app->verbose("  Sent mails ");
    $app->verbose("    Reminder FIRST: " . $counters[Reminders::FIRST]);
    $app->verbose("    Reminder SECOND: " . $counters[Reminders::SECOND]);
    $app->verbose("    Reminder THIRD: " . $counters[Reminders::THIRD]);

});
