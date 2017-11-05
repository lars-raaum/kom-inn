<?php

namespace crons\tasks;

use app\mails\Purge;
use app\models\People;

class UpdatePeople
{

    /** @var \app\Cli */
    protected $app;
    /** @var array */
    protected $counters;

    public function __construct(\app\Cli $app)
    {
        $this->app = $app;
        $counter_keys = ["EMAIL", "DELETE", "PURGE", "ERROR", "EXPIRE", "TOTAL"];
        $fn = function ($o, $v) { $o[$v] = 0; return $o; };
        $this->counters = array_reduce($counter_keys, $fn, []);
    }

    /**
     * Set all people unused for 60 days to expired and send them an "reactiviate mail".
     * Also delete all people that have been expired for 30 days
     */
    public function task()
    {
        $app = $this->app;
        /* @var $mailer \app\Mailer */
        $mailer = $this->app['mailer'];
        /* @var $people \app\models\People */
        $people = $this->app['people'];

        // Get all active users that hasnt been updated in the last 60 days and EXPIRE them
        $sql = "SELECT p.*, g.id as `guest_id`, h.id as `host_id` FROM people AS p ".
            "LEFT JOIN guests AS g ON (p.id = g.user_id) ".
            "LEFT JOIN hosts  AS h ON (p.id = h.user_id) ".
            "WHERE p.updated < DATE_ADD(CURDATE(), INTERVAL - 60 DAY) AND p.status = 1 ORDER BY p.id ASC";
        $result = $this->getPeople($sql);
        foreach ($result as $person) {
            if ($person['guest_id'] === NULL) {
                $person['type'] = People::TYPE_HOST;
            }
            if ($person['host_id'] === NULL) {
                $person['type'] = People::TYPE_GUEST;
            }
            try {
                $expired = $app['dry'] || $people->setToExpired($person['id']);
                if ($expired) {
                    $this->counters['EXPIRE']++;
                }
                if ($person['type'] === People::TYPE_GUEST) {
                    $sent = $app['dry'] || $mailer->sendGuestExpired($person);
                    $mail_sent = Purge::EXPIRED_GUEST;
                } else {
                    $sent = $app['dry'] || $mailer->sendHostExpired($person);
                    $mail_sent = Purge::EXPIRED_HOST;
                }
                if ($sent) {
                    $this->counters['EMAIL']++;
                    $this->app->verbose("Sent mail [{$mail_sent}] to [{$person['id']}] {$person['name']}");
                }
                $this->app->verbose("Person [{$person['id']}] {$person['name']} - Expired");
            } catch (\app\Exception $e) {
                $app->error("ERROR! " . $e->getMessage());
                $this->counters['ERROR']++;
            } catch (\Exception $e) {
                error_log("Failed to handle person {$person['id']} : " . $e->getMessage());
                $app->verbose(" ");
                $this->counters['ERROR']++;
                $this->counters['TOTAL']++;
                break;
            }
            $app->verbose(" ");
            $this->counters['TOTAL']++;
        }

        // Get all soft deleted and expired users that hasnt been updated in another 30 days and PURGE them
        $sql = "SELECT * FROM people WHERE updated < DATE_ADD(CURDATE(), INTERVAL - 30 DAY) AND status IN (-1, -2) ORDER BY id ASC";
        $result = $this->getPeople($sql);
        foreach ($result as $person) {
            try {
                $purged = $app['dry'] || $people->purge($person['id']);
                if ($purged) {
                    $this->counters['PURGE']++;
                }
                $this->app->verbose("Person [{$person['id']}] {$person['name']} - Purged");
            } catch (\app\Exception $e) {
                $app->error("ERROR! " . $e->getMessage());
                $this->counters['ERROR']++;
            } catch (\Exception $e) {
                error_log("Failed to handle person {$person['id']} : " . $e->getMessage());
                $app->verbose(" ");
                $this->counters['ERROR']++;
                $this->counters['TOTAL']++;
                break;
            }
            $app->verbose(" ");
            $this->counters['TOTAL']++;
        }

        // Get all used users that hasnt been updated in the last 60 days and SOFT DELETE
        $sql = "SELECT p.*, g.id as `guest_id`, h.id as `host_id` FROM people AS p ".
            "LEFT JOIN guests AS g ON (p.id = g.user_id) ".
            "LEFT JOIN hosts  AS h ON (p.id = h.user_id) ".
            "WHERE p.updated < DATE_ADD(CURDATE(), INTERVAL - 60 DAY) AND p.status = 2 ORDER BY p.id ASC";
        $result = $this->getPeople($sql);
        foreach ($result as $person) {
            if ($person['guest_id'] === NULL) {
                $person['type'] = People::TYPE_HOST;
            }
            if ($person['host_id'] === NULL) {
                $person['type'] = People::TYPE_GUEST;
            }
            try {
                $deleted = $app['dry'] || $people->setToSoftDeleted($person['id']);
                if ($deleted) {
                    $this->counters['DELETE']++;
                }
                if ($person['type'] == People::TYPE_GUEST) {
                    $sent = $app['dry'] || $mailer->sendReactivateUsedGuest($person);
                    $mail_sent = Purge::REACTIVATE_GUEST;
                } else {
                    $sent = $app['dry'] || $mailer->sendReactivateUsedHost($person);
                    $mail_sent = Purge::REACTIVATE_HOST;
                }
                if ($sent) {
                    $this->counters['EMAIL']++;
                    $this->app->verbose("Sent mail [{$mail_sent}] to [{$person['id']}] {$person['name']}");
                }
                $this->app->verbose("Person [{$person['id']}] {$person['name']} - Soft deleted");
            } catch (\app\Exception $e) {
                $app->error("ERROR! " . $e->getMessage());
                $this->counters['ERROR']++;
            } catch (\Exception $e) {
                error_log("Failed to handle person {$person['id']} : " . $e->getMessage());
                $app->verbose(" ");
                $this->counters['ERROR']++;
                $this->counters['TOTAL']++;
                break;
            }
            $app->verbose(" ");
            $this->counters['TOTAL']++;
        }

        $app['logger']->debug(__CLASS__ . " RESULTS: " . http_build_query($this->counters, '', ' | '));
        $this->outputResults();
    }

    private function getPeople($sql) : array
    {
        $app = $this->app;
        if (isset($app['max'])) {
            $limit = (int) $app['max'];
            $sql .= "LIMIT {$limit}";
        }

        $app['logger']->info("SQL [ $sql ] - by [{$app['PHP_AUTH_USER']}]");
        $app->verbose("SQL [ $sql ] - by [{$app['PHP_AUTH_USER'] }]", "");
        $people = $app['db']->fetchAll($sql);
        $total = count($people);
        $app->verbose("Found {$total} active people", "");
        return $people;
    }

    protected function outputResults()
    {
        $app = $this->app;
        $app->verbose(" ", " Handled: " . $this->counters['TOTAL']);
        $app->verbose("  Emails: " . $this->counters['EMAIL']);
        $app->verbose("  Expired: " . $this->counters['EXPIRE']);
        $app->verbose("  Deleted: " . $this->counters['DELETE']);
        $app->verbose("  Purged: " . $this->counters['PURGE']);
        if ($this->counters['ERROR'])
            $app->verbose("  Errors: " . $this->counters['ERROR']);
    }
}
