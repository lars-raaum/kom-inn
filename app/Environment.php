<?php

namespace app;

class Environment
{

    public static function get($name)
    {
        $env = static::env();
        $all = require_once RESOURCE_PATH . '/environments.php';
        if (!array_key_exists($env, $all)) {
            error_log("Environment {$env} is not configured!");
            throw new \Exception("Environment {$name} is not configured!");
        }

        if (!array_key_exists($name, $all[$env])) {
            error_log("Environmental config {$name} not configured for {$env}");
            return null;
        }

        return $all[$env][$name];
    }

    private static function env()
    {
        switch ($_SERVER['HTTP_HOST']) {
            case 'localhost:9001':
                return 'local';
            case 'admin.dev.kom-inn.org':
                return 'dev';
            default:
            case 'admin.kom-inn.org':
                return 'pro';
        }
    }
}
