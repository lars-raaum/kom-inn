<?php

namespace app;

use Twilio\Rest\Client;

class Sms implements \Pimple\ServiceProviderInterface
{

    protected $client;
    protected $from;
    protected $admin;
    protected $prefix;

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

    public function __construct(array $config) {
        if (empty($config)) return;
        $this->from = $config['phone'];
        $this->admin = isset($config['admin']) ? $config['admin'] : false;
        $this->client = new Client($config['sid'], $config['token']);
        $this->prefix = isset($config['prefix']) ? $config['prefix'] : '';
    }

    public function sendHostInform(array $match) {
        if (empty($this->client)) return false;
        try {
            error_log("Sending SMS to {$match['host']['phone']}");
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

