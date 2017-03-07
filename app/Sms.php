<?php

namespace app;

use Twilio\Rest\Client;

/**
 * Class Sms
 */
class Sms implements \Pimple\ServiceProviderInterface
{

    /**
     * @var \Twilio\Rest\Client
     */
    protected $client;
    /**
     * @var string
     */
    protected $from;
    /**
     * @var bool|string
     */
    protected $admin;
    /**
     * @var string
     */
    protected $prefix;

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
        $app['sms'] = $this;
    }

    /**
     * Sms constructor.
     */
    public function __construct() {
        $config = Environment::get('sms');
        if ($config['enabled'] == false) {
            return;
        }
        $this->from = $config['phone'] ?? null;
        $this->admin = $config['admin'] ?? false;
        $this->client = new Client($config['sid'], $config['token']);
        $this->prefix = $config['prefix'] ?? '';
    }

    /**
     * @param array $match
     * @return bool|\Twilio\Rest\Api\V2010\Account\MessageInstance
     */
    public function sendHostInform(array $match) {
        if (empty($this->client)) return false;
        try {
            $this->app['monolog']->info("Sending SMS to {$match['host']['phone']}");
            return $this->client->messages->create(
                $match['host']['phone'],
                [
                    'from' => $this->from,
                    'body' => $this->prefix . "Hei! En Kom inn-gjest er klar for en middagsinvitasjon fra deg! Du vil snart motta mer informasjon på epost :)"
                ]
            );
        } catch (\Exception $e) {
            error_log("Failed to send SMS: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @return bool|\Twilio\Rest\Api\V2010\Account\MessageInstance
     */
    public function sendAdminRegistrationNotice() {
        if (empty($this->client) || empty($this->admin)) return false;
        try {
            return $this->client->messages->create(
                $this->admin,
                [
                    'from' => $this->from,
                    'body' => $this->prefix . "Ny gjest!\nHvor er det husly? Trenger hjerterom!\n - Kom-Inn"
                ]
            );
        } catch (\Exception $e) {
            error_log("Failed to send SMS: " . $e->getMessage());
            return false;
        }

    }
}

