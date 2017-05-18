<?php

namespace app;

use app\mails\Purge;
use app\models\Emails;
use InvalidArgumentException;
use Mailgun\Mailgun;
use app\mails\Reminders;
use app\mails\HostInform;

/**
 * Class Emailing
 *
 */
class Mailer implements \Pimple\ServiceProviderInterface
{

    /**
     * @var Mailgun
     */
    protected $client;
    /**
     * @var string
     */
    protected $domain;
    /**
     * @var bool|mixed
     */
    protected $admin;
    /**
     * @var string
     */
    protected $prefix;
    /**
     * @var \Pimple\Container
     */
    protected $app;
    /**
     * @var string
     */
    protected $salt;
    /**
     * @var string
     */
    protected $from;

    /**
     * Registers this model in the app and gives it access to @app
     *
     * @param \Pimple\Container $app
     */
    public function register(\Pimple\Container $app)
    {
        $this->app = $app;
        $app['mailer'] = $this;
    }

    /**
     * Emailing constructor, if config is empty, no emails will be called
     * even if called
     *
     */
    public function __construct()
    {
        $config = Environment::get('emails');
        $this->admin  = $config['admin'] ?? false;
        $this->prefix = $config['prefix'];
        $this->salt   = $config['salt'] ?? 'kioslo';

        if ($config['enabled'] == false) {
            return;
        }

        $this->client = new Mailgun($config['key']);
        $this->domain = $config['domain'];
        $this->from   = $config['from'];
    }

    /**
     * Support method that generates salted hashcode from provided emails
     *
     * @param string $email
     * @return string
     */
    public function createHashCode(string $email) : string
    {
        return sha1($this->salt . $email);
    }

    /**
     * Send admin mail notice that a new guest has registered
     *
     * Automatically disabled if `admin` is not configured
     *
     * @return bool
     */
    public function sendAdminRegistrationNotice() : bool
    {
        if (empty($this->client) || empty($this->admin)) return false;
        $this->app['logger']->debug("Sent admin registration notice to {$this->admin}");
        $this->client->sendMessage($this->domain, [
            'from'    => $this->from,
            'to'      => $this->admin,
            'subject' => $this->prefix . 'Kom inn: Ny gjest',
            'html'    => '<h1>Ny gjest</h1><p><a href="http://kom-inn.org/admin">Finn match</a></p>'
        ]);
        return true;
    }

    /**
     * Send update request mail to host after match has been verified some time ago
     *
     * @param array $match
     * @param string $type
     * @return bool
     * @throws \app\Exception if email is not configured
     * @throws InvalidArgumentException if $type is not a valid type
     */
    public function sendReminderMail(array $match, string $type = Reminders::NEUTRAL)
    {
        if (empty($this->client)) {
            throw new \app\Exception("Emailing is not configured");
        }
        if (!in_array($type, Reminders::$TYPES)) {
            throw new InvalidArgumentException("Type $type is not valid Reminder type");
        }
        $email_data = [
            'user_id' => $match['host_id'],
            'match_id' => $match['id'],
            'type' => $type,
        ];
        $templates = new Reminders($this);
        switch ($type) {
            case Reminders::FIRST:
                $body = $templates->buildFeedbackRequestText2days($match);
                break;
            case Reminders::SECOND:
                $body = $templates->buildFeedbackRequestText4days($match);
                break;
            case Reminders::THIRD:
                $body = $templates->buildFeedbackRequestText7days($match);
                break;
            case Reminders::NEUTRAL:
            default:
                $body = $templates->buildFeedbackRequestText($match);
        }

        $to = $match['host']['email'];

        try {
            $this->client->sendMessage($this->domain, [
                'from'    => $this->from,
                'to'      => $to,
                'subject' => $this->prefix . 'Kom inn: oppfølgning - hvordan gikk det?',
                'html'    => $body
            ]);
            $sent = true;
            $this->app['logger']->debug("SENT {$type} email to person {$match['host_id']}");
        } catch (\Exception $e) {
            error_log("Failed to mail : " . $e->getMessage());
            $this->app['logger']->error("FAILED to send {$type} email to person {$match['host_id']}");
            $email_data['status'] = Emails::STATUS_FAILED;
            $sent = false;
        }
        $this->app['emails']->insert($email_data);
        return $sent;
    }

    /**
     * Send mail to host upon match created
     *
     * @param array $match
     * @return bool
     */
    public function sendHostInform(array $match) : bool
    {
        if (empty($this->client)) return false;
        $to = $match['host']['email'];
        $email_data = [
            'user_id' => $match['host_id'],
            'match_id' => $match['id'],
            'type' => HostInform::HOST_INFORM
        ];
        try {
            $templater = new HostInform($this);
            $body = $templater->buildHostInformText($match);
            $this->client->sendMessage($this->domain, [
                'from'    => $this->from,
                'to'      => $to,
                'subject' => $this->prefix . 'Kom inn: Gjester venter på en invitasjon fra deg',
                'html'    => $body
            ]);
            $sent = true;
        } catch (\Exception $e) {
            error_log("Failed to mail : " . $e->getMessage());
            $email_data['status'] = Emails::STATUS_FAILED;
            $sent = false;
        }
        $this->app['emails']->insert($email_data);
        return $sent;
    }

    public function sendHostExpired(array $host) : bool
    {
        if (empty($this->client)) return false;
        $to = $host['email'];
        $email_data = [
            'user_id' => $host['id'],
            'type' => Purge::EXPIRED_HOST
        ];
        try {
            $templater = new Purge($this);
            $body = $templater->buildExpiredHostText($host);
            $this->client->sendMessage($this->domain, [
                'from'    => $this->from,
                'to'      => $to,
                'subject' => $this->prefix . 'Vil du fortsatt invitere på middag? Gi oss beskjed!',
                'html'    => $body
            ]);
            $sent = true;
        } catch (\Exception $e) {
            error_log("Failed to mail : " . $e->getMessage());
            $email_data['status'] = Emails::STATUS_FAILED;
            $sent = false;
        }
        $this->app['emails']->insert($email_data);
        return $sent;
    }

    public function sendReactivateUsed(array $person) : bool
    {
        if (empty($this->client)) return false;
        $to = $person['email'];
        $email_data = [
            'user_id' => $person['id'],
            'type' => Purge::REACTIVATE_PERSON
        ];
        try {
            $templater = new Purge($this);
            $body = $templater->buildReactivateUsedText($person);
            $this->client->sendMessage($this->domain, [
                'from'    => $this->from,
                'to'      => $to,
                'subject' => $this->prefix . 'Vil du på en ny Kom inn-middag?',
                'html'    => $body
            ]);
            $sent = true;
        } catch (\Exception $e) {
            error_log("Failed to mail : " . $e->getMessage());
            $email_data['status'] = Emails::STATUS_FAILED;
            $sent = false;
        }
        $this->app['emails']->insert($email_data);
        return $sent;
    }
}
