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
        $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
        error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
        return $this->app['db']->fetchAssoc($sql, [(int) $id]);
    }
}
