<?php

namespace app\models;

use app\exceptions\ApiException;
use DateTime;
use Doctrine\DBAL\Types\Type;

class Matches implements \Pimple\ServiceProviderInterface
{
    const STATUS_DELETED = -1;
    const STATUS_NEW = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_EXECUTED = 2;

    /**
     * @var \Silex\Application
     */
    protected $app;

    /**
     * Registers this model in the app and gives it access to @app
     *
     * @param \Pimple\Container $app
     */
    public function register(\Pimple\Container $app)
    {
        $this->app = $app;
        $app['matches'] = $this;
    }

    /**
     * Returns a single Guest
     *
     * @param int $id
     * @param bool $with_host
     * @param bool $with_guest
     * @return array|false
     * @throws ApiException if not found
     */
    public function get(int $id, bool $with_host = true, bool $with_guest = true)
    {
        if ($id === 0) throw new ApiException("Id not valid", 404);

        $args = [$id];
        $sql = "SELECT * FROM matches WHERE id = ?";
        $this->app['logger']->info("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        $match = $this->app['db']->fetchAssoc($sql, $args);
        if (!$match) {
            throw new ApiException("Match $id not found", 404);
        }
        if ($with_host) {
            $match['host'] = $this->app['hosts']->get((int) $match['host_id']);
        }
        if ($with_guest) {
            $match['guest'] = $this->app['guests']->get((int) $match['guest_id']);
        }

        return $match;
    }

    /**
     * Find list of matches based on status, optionally include hosts and guests
     *
     * @param int $status
     * @param bool $with_host
     * @param bool $with_guest
     * @return array
     */
    public function find(int $status, bool $with_host = true, bool $with_guest = true) : array
    {
        // TODO join requests
        $args = [$status];
        $sql = "SELECT * FROM matches WHERE status = ? ORDER BY id DESC";
        $this->app['logger']->info("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        $matches = $this->app['db']->fetchAll($sql, $args);

        if ($with_guest || $with_host) {
            foreach ($matches as $k => $match) {
                try {
                    if ($with_host) {
                        $matches[$k]['host'] = $this->app['hosts']->get((int) $match['host_id']);
                    }
                    if ($with_guest) {
                        $matches[$k]['guest'] = $this->app['guests']->get((int) $match['guest_id']);
                    }
                } catch(\app\exceptions\ApiException $e) {
                    unset($matches[$k]);
                }
            }
        }


        return $matches;
    }

    /**
     * Find list of matches based on people id
     *
     * @param int $people_id
     * @return array
     */
    public function findByPeopleId($people_id)
    {
        $args = [$people_id, $people_id];
        $sql = "SELECT * FROM matches WHERE host_id = ? OR guest_id = ? ORDER BY id DESC";
        error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        return $this->app['db']->fetchAll($sql, $args);
    }

    /**
     * Insert a new match
     *
     * @param array $data
     * @return int pk
     */
    public function insert(array $data)
    {
        $now = new DateTime('now');
        $data['updated'] = $now;
        $data['created'] = $now;
        $dtt = Type::getType('datetime');
        $types = ['updated' => $dtt, 'created' => $dtt];

        $this->app['logger']->info("INSERT match Guest[{$data['guest_id']}] Host[{$data['host_id']}] by [{$this->app['PHP_AUTH_USER']}]");
        $result = $this->app['db']->insert('matches', $data, $types);
        if (!$result) {
            error_log("ERROR: Failed to insert new match!");
            return false;
        }

        $id = $this->app['db']->lastInsertId();
        return $id;
    }

    /**
     * Update match
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data) : bool
    {
        $types = ['updated' => Type::getType('datetime')];
        $data['updated'] = new DateTime('now');

        $this->app['logger']->info("UPDATE Match[{$id}] by [{$this->app['PHP_AUTH_USER']}]");
        $result = $this->app['db']->update('matches', $data, ['id' => $id], $types);
        if (!$result) {
            // @TODO grab sql error to log?
            error_log("ERROR: Failed to update match {$id}");
            return false;
        }
        return true;
    }

    /**
     * Soft delete match
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id) : bool
    {
        $types = ['updated' => Type::getType('datetime')];
        $data  = [
            'status'  => Matches::STATUS_DELETED,
            'updated' => new DateTime('now')
        ];
        $this->app['logger']->info("SOFT DELETE Match[{$id}] by [{$this->app['PHP_AUTH_USER']}]");
        $result = $this->app['db']->update('matches', $data, ['id' => $id], $types);
        if (!$result) {
            error_log("ERROR: Failed to update match");
            return false;
        }
        return true;
    }
}
