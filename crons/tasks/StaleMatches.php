<?php

namespace crons\tasks;

use app\mails\Reminders;

class StaleMatches {

    /** @var \app\Cli */
    protected $app;
    /** @var array */
    protected $counters;

    public function __construct(\app\Cli $app)
    {
        $this->app = $app;
        $counter_keys = array_merge(Reminders::$TYPES, ["DELETE", "SKIP", "ERROR", "TOTAL"]);
        $fn = function($o, $v) { $o[$v] = 0; return $o; };
        $this->counters = array_reduce($counter_keys, $fn, []);
    }

    public function task()
    {
        $matches = $this->getMatches();
        $app = $this->app;
        foreach ($matches as $match) {
            try {
                $this->handleMatch($match);
            } catch (\app\Exception $e) {
                $app->error("ERROR! " . $e->getMessage());
                $this->counters['ERROR']++;
            } catch (\Exception $e) {
                error_log("Failed to handle match {$match['id']} : " . $e->getMessage());
                $app->verbose(" ");
                $this->counters['ERROR']++;
                $this->counters['TOTAL']++;
                break;
            }
            $app->verbose(" ");
            $this->counters['TOTAL']++;

        }
        $app['logger']->debug(__CLASS__ . " RESULTS: " . http_build_query($this->counters, '', ' | '));
        $this->outputResults($app);
    }

    protected function getMatches(): array
    {
        $app = $this->app;
        if (isset($app['max'])) {
            $limit = (int)$app['max'];
        }
        if (isset($limit)) {
            $sql = "SELECT *, DATEDIFF(NOW(), `created`) as `ago` FROM matches WHERE status = 0 ORDER BY id DESC LIMIT {$limit}";
        } else {
            $sql = "SELECT *, DATEDIFF(NOW(), `created`) as `ago` FROM matches WHERE status = 0 ORDER BY id DESC";
        }

        $app['logger']->info("SQL [ $sql ] - by [{$app['PHP_AUTH_USER']}]");
        $app->verbose("SQL [ $sql ] - by [{$app['PHP_AUTH_USER'] }]", "");
        $matches = $app['db']->fetchAll($sql);
        $total = count($matches);
        $app->verbose("Found {$total} active matches", "");
        return $matches;
    }

    /* Tasks:
    - Match created between 2 and 4 days ago, send first reminder mail
    - Match created between 4 and 7 days ago, send second reminder mail
    - Match created between 7 and 9 days ago, send third reminder mail
    - Match created more than 9 days ago and still unconfirmed, cancel match and delete host
    */
    protected function handleMatch($match)
    {
        $app = $this->app;
        $app->verbose("Match {$match['id']}");

        $created = $match['ago'];
        if ($created < 2) { // It is new, do nothing
            $this->counters['SKIP']++;
        } elseif ($created > 9) {// Match is too old, lets cancel it
            $this->cancelMatch($match);
        } elseif ($created >= 2 && $created <= 3) {
            $this->sendReminder($match, Reminders::FIRST);
        } elseif ($created >= 4 && $created <= 6) {
            $this->sendReminder($match, Reminders::SECOND);
        } elseif ($created >= 7 && $created <= 9) {
            $this->sendReminder($match, Reminders::THIRD);
        }
    }

    protected function cancelMatch(array $match)
    {
        $app = $this->app;
        if ($app['dry'] == false) {
            $app['matches']->delete($match['id']);
            $app['people']->setToSoftDeleted($match['host_id']);
            $app['people']->setToActive($match['guest_id']);
            $app['mailer']->sendAdminRegistrationNotice(); // Does potentially send duplicates yes.
        }
        $app->verbose("Deleting host [{$match['host_id']}]");
        $app->verbose("Setting guest to active [{$match['guest_id']}]");
        $app->verbose("Cancelling match [{$match['id']}]");
        $this->counters['DELETE']++;
    }

    protected function sendReminder(array $match, string $type)
    {
        $app = $this->app;
        /** @var \app\Mailer $mailer */
        $mailer = $app['mailer'];
        if ($app->getEmailsModel()->find($match['host_id'], $type)) {
            $this->counters['SKIP']++;
            return;
        }
        $match['host'] = $app['hosts']->get($match['host_id']);
        if ($app['dry'] == false) {
            $mailer->sendReminderMail($match, $type);
        }
        $app->verbose($type . " sent to [{$match['host']['email']}]");
        $this->counters[$type]++;
    }

    protected function outputResults(\app\Cli $app)
    {
        $app->verbose(" ", " Handled: " . $this->counters['TOTAL']);
        $app->verbose("  Skipped: " . $this->counters['SKIP']);
        $app->verbose("  Deleted: " . $this->counters['DELETE']);
        $app->verbose("  Sent mails ");
        $app->verbose("    Reminder FIRST: " . $this->counters[Reminders::FIRST]);
        $app->verbose("    Reminder SECOND: " . $this->counters[Reminders::SECOND]);
        $app->verbose("    Reminder THIRD: " . $this->counters[Reminders::THIRD]);
        if ($this->counters['ERROR'])
            $app->verbose("  Errors: " . $this->counters['ERROR']);
    }
}

