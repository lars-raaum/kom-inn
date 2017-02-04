<?php

namespace app\models;

class Matches implements \Pimple\ServiceProviderInterface
{
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
     * @return array
     */
    public function get(int $id, bool $with_host = true, bool $with_guest = true)
    {

        $args = [$id];
        $sql = "SELECT * FROM matches WHERE id = ?";
        error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
        $match = $this->app['db']->fetchAssoc($sql, $args);
        if (!$match) {
            return false;
        }
        if ($with_host) {
            $match['host'] = $this->app['hosts']->get((int) $match['host_id']);
        }
        if ($with_guest) {
            $match['guest'] = $this->app['guests']->get((int) $match['guest_id']);
        }

        return $match;
    }

    public function find(int $status, bool $with_host = true, bool $with_guest = true)
    {
        // TODO join requests
        $args = [$status];
        $sql = "SELECT * FROM matches WHERE status = ? ORDER BY id DESC";
        error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
        $matches = $this->app['db']->fetchAll($sql, $args);

        if ($with_guest || $with_host) {
            foreach ($matches as $k => $match) {
                if ($with_host) {
                    $matches[$k]['host'] = $this->app['hosts']->get((int) $match['host_id']);
                }
                if ($with_guest) {
                    $matches[$k]['guest'] = $this->app['guests']->get((int) $match['guest_id']);
                }
            }
        }
        return $matches;
    }

    public function update(int $id, array $data)
    {
        $types = ['updated' => \Doctrine\DBAL\Types\Type::getType('datetime')];
        $data['updated'] = new DateTime('now');

        error_log("Update Match[{$id}] by [{$_SERVER['PHP_AUTH_USER']}]");
        $result = $this->app['db']->update('matches', $data, ['id' => $id], $types);
        if (!$result) {
            // @TODO grab sql error to log?
            error_log("Failed to update match {$id}");
            return false;
        }
        return true;
    }
}
