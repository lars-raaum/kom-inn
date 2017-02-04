<?php

namespace app\models;

class Hosts implements \Pimple\ServiceProviderInterface
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
        $app['hosts'] = $this;
    }

    /**
     * Returns a single Host
     *
     * @param int $id
     * @return array
     */
    public function get(int $id)
    {
        $args = [$id];
        $sql = "SELECT people.*, hosts.id AS `host_id` FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
        error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        $host = $this->app['db']->fetchAssoc($sql, $args);
        if (!$host) {
            return false;
        }
        $host['type'] = People::TYPE_HOST;
        return $host;
    }
}
