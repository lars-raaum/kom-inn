<?php

namespace app;

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
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->admin  = isset($config['admin']) ? $config['admin'] : false;
        $this->prefix = isset($config['prefix']) ? $config['prefix'] : '';
        $this->salt   = isset($config['salt']) ? $config['salt'] : 'kioslo';

        if (empty($config)) return;

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
    public function sendReminderMail(array $match, string $type = Reminders::NEUTRAL) : bool
    {
        if (empty($this->client)) {
            throw new \app\Exception("Emailing is not configured");
        }
        if (!in_array($type, Reminders::$TYPES)) {
            throw new InvalidArgumentException("Type $type is not valid Reminder type");
        }
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
        $this->client->sendMessage($this->domain, [
            'from'    => $this->from,
            'to'      => $to,
            'subject' => $this->prefix . 'Kom inn: oppfølgning - hvordan gikk det?',
            'html'    => $body
        ]);
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
        try {
            $templater = new HostInform($this);
            $body = $templater->buildHostInformText($match);
            $this->client->sendMessage($this->domain, [
                'from'    => $this->from,
                'to'      => $to,
                'subject' => $this->prefix . 'Kom inn: Gjester venter på en invitasjon fra deg',
                'html'    => $body
            ]);
        } catch (\Exception $e) {
            error_log("Failed to mail : " . $e->getMessage());
            return false;
        }
        return true;
    }

}
