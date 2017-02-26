<?php

namespace app\models;

use app\mails\Reminders;
use DateTime;
use Doctrine\DBAL\Types\Type;

/**
 * Class Emails
 * @package app\models
 */
class Emails implements \Pimple\ServiceProviderInterface
{
    CONST STATUS_SENT = "SENT";
    CONST STATUS_CONFIRMED = "CONFIRMED";
    CONST STATUS_FAILED = "FAILED";

    /**
     * @var \Silex\Application
     */
    protected $app;

    /**
     * Registers this model in the app and gives it access to @app
     *
     * @param \Pimple\Container 
     */
    public function register(\Pimple\Container $app)
    {
        $this->app = $app;
        $app['emails'] = $this;
    }

    /**
     * Returns a single email request records
     *
     * @param int $id
     * @return array|false
     */
    public function get(int $id)
    {
        if ($id === 0) return false;

        $args = [$id];
        $sql = "SELECT * FROM emails WHERE id = ?";
        error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        $record = $this->app['db']->fetchAssoc($sql, $args);
        if (!$record) {
            return false;
        }
        return $record;
    }

    /**
     * Check if email has been sent to person
     *
     * @param int $person_id
     * @param string $type
     * @return array
     */
    public function find(int $person_id, string $type) : array
    {
        $args = [$person_id, $type];
        $sql = "SELECT * FROM matches WHERE user_id = ? AND type = ? ";
        error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        return $this->app['db']->fetchAll($sql, $args);
    }

    /**
     * Create new record, data required to contain `user_id` and `type`
     *
     * @param array $data
     * @throws \Exception if unable to insert
     */
    public function insert(array $data)
    {
        $now = new DateTime('now');
        $dtt = Type::getType('datetime');
        $types = ['updated' => $dtt, 'created' => $dtt];
        $data['updated'] = $now;
        $data['created'] = $now;
        $data['status']  =
        $result = $this->app['db']->insert('emails', $data, $types);
        if (!$result) {
            throw new \Exception("Failed to insert email record!");
        }
    }
}
