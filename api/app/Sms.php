<?php

namespace app;

use Twilio\Rest\Client;

class Sms {

    protected $client;
    protected $from;

    public function __construct() {
        $config = require_once RESOURCE_PATH . '/sms.php';
        if (empty($config)) return;
        $this->from = $config['phone'];
        $this->client = new Client($config['sid'], $config['token']);
    }

    public function sendHostInform(array $match) {
        if (empty($this->client)) return;
        return $this->client->messages->create(
            $match['host']['phone'],
            [
                'from' => $this->from,
                'body' => "Hei! En Kom inn-gjest er klar for en middagsinvitasjon fra deg! Mer informasjon på epost :)"
            ]
        );
    }
}

