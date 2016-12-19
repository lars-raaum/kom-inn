<?php

namespace app;

use Twilio\Rest\Client;

class Sms {

    protected $client;
    protected $from;
    protected $admin;

    public function __construct() {
        $config = require_once RESOURCE_PATH . '/sms.php';
        if (empty($config)) return;
        $this->from = $config['phone'];
        $this->admin = isset($config['admin']) ? $config['admin'] : false;
        $this->client = new Client($config['sid'], $config['token']);
    }

    public function sendHostInform(array $match) {
        if (empty($this->client)) return;
        try {
            return $this->client->messages->create(
                $match['host']['phone'],
                [
                    'from' => $this->from,
                    'body' => "Hei! En Kom inn-gjest er klar for en middagsinvitasjon fra deg! Mer informasjon på epost :)"
                ]
            );
        } catch (\Exception $e) {
            error_log("Failed to send SMS: " . $e->getMessage());
        }
    }

    public function sendAdminRegistrationNotice() {
        if (empty($this->client) || empty($this->admin)) return;
        try {
            return $this->client->messages->create(
                $this->admin,
                [
                    'from' => $this->from,
                    'body' => "Ny gjest!\nHvor er det husly? Trenger hjerterom!\n - Kom-Inn"
                ]
            );
        } catch (\Exception $e) {
            error_log("Failed to send SMS: " . $e->getMessage());
        }

    }
}

