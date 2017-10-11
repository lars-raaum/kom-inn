<?php

namespace app;

/**
 * Class Environment
 *
 */
class Environment
{

    // dev, pre, pro
    /** @var string */
    private static $env = null;

    /** @var array */
    private static $config = null;

    /**
     * Get an environment config by name
     *
     * @param $name
     * @return mixed
     * @throws \Exception if $name is not set
     */
    public static function get(string $name)
    {
        if (!self::$config) {
            self::setConfig();
        }
        if (!array_key_exists($name, self::$config)) {
            $env = self::env();
            error_log("Environmental config {$name} not configured for {$env}");
            return null;
        }
        return self::$config[$name];
    }

    private static function setConfig()
    {
        $env = static::env();
        $all = require RESOURCE_PATH . '/environments.php';
        if (!array_key_exists($env, $all)) {
            error_log("Environment {$env} is not configured!");
            throw new \Exception("Environment {$name} is not configured!");
        }
        self::$config = $all[$env];
    }

    /**
     * @return string
     */
    public static function env()
    {
        if (!self::$env) {
            self::$env = include RESOURCE_PATH . '/env-dist.php';
        }
        return self::$env;
    }
}
