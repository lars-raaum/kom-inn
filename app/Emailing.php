<?php

namespace app;

use Mailgun\Mailgun;

/**
 * Class Emailing
 *
 */
class Emailing implements \Pimple\ServiceProviderInterface
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
        $app['email'] = $this;
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
     * @return bool
     */
    public function sendNaggingMail(array $match) : bool
    {
        if (empty($this->client)) return false;
        $to = $match['host']['email'];
        try {
            $this->client->sendMessage($this->domain, [
                'from'    => $this->from,
                'to'      => $to,
                'subject' => $this->prefix . 'Kom inn: oppfølgning - hvordan gikk det?',
                'html'    => $this->buildFeedbackRequestText($match)
            ]);
        } catch (\Exception $e) {
            error_log("Failed to mail : " . $e->getMessage());
            return false;
        }
        return true;
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
            $this->client->sendMessage($this->domain, [
                'from'    => $this->from,
                'to'      => $to,
                'subject' => $this->prefix . 'Kom inn: Gjester venter på en invitasjon fra deg',
                'html'    => $this->buildHostInformText($match)
            ]);
        } catch (\Exception $e) {
            error_log("Failed to mail : " . $e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Build text to use in HostInform mail
     *
     * @param array $match
     * @return string
     */
    protected function buildHostInformText(array $match) : string
    {
        $name       = $match['host']['name'];
        $guestname  = $match['guest']['name'];
        $age        = $match['guest']['age'];
        $origin     = $match['guest']['origin'];
        $verter     = $match['host']['adults_m'] + $match['host']['adults_f'] + $match['host']['children'];
        $gjester    = $match['guest']['adults_m'] + $match['guest']['adults_f'] + $match['guest']['children'];
        $dudere     = $verter > 0 ? 'dere' : 'du';
        $HanHun     = $match['guest']['gender'] == 'male' ? 'Han' : 'Hun';
        $han_hun    = $match['guest']['gender'] == 'male' ? 'han' : 'hun';

        $text = "<h1>Hei {$name}</h1>\n\n
        <p>Først og fremst takk for at $dudere vil være med på Kom inn!</p>\n
        <p>Vi håper $dudere er klare for middag :)</p>\n
        <h3>Hva skjer nå?</h3>\n
        <p>Vi har funnet gjester som gjerne vil komme på besøk.</p>\n";
        $text .= "<p>$HanHun heter $guestname. $HanHun er $age år. $HanHun er fra $origin</p>\n";
        if ($gjester > 0) {
            $text .= "<p>$HanHun har oppgitt at $han_hun har med seg $gjester gjester på middag";
            $text .= ((empty($match['guest']['bringing'])) ? "." : ": " . $match['guest']['bringing']) . "</p>\n";
        } else {
            $text .= "<p>$HanHun kommer aleine.</p>\n";
        }

        $food_concerns = $match['guest']['food_concerns'];
        if ($food_concerns) {
            $text .= "<p>På spørsmål om det er noe $han_hun ikke spiser, svarer $han_hun: {$food_concerns}</p>\n";
        }

        $text .= "<p>Det kan være lurt å spørre om dette en ekstra gang når dere har opprettet kontakt. </p>\n".
        "<p>Selve invitasjonen står du for selv slik at eventuell videre kommunikasjonen kan skje direkte. Vi anbefaler at du bruker SMS.</p>\n".
        "<p>Skriv meldingen på norsk, nevn gjerne \"Kom inn\" og husk å få med tidspunkt/adresse.</p>\n".
        "<p>Dersom du ikke hører noe på SMS, så anbefaler vi at du tar en telefon. Noen er komfortable med å snakke norsk, men synes skriftlig er vanskelig.</p>\n";

        $guestphonenumber   = $match['guest']['phone'];
        $text .= "<p>Telefonnummeret til {$guestname} er <strong>{$guestphonenumber}</strong></p>\n";
        $text .= "<p>Vi opplever dessverre at en god del verter ikke sender en invitasjon, så vi ber deg
        invitere i løpet av 48 timer og gi oss beskjed. Selve avtalen trenger ikke å være i løpet av de nærmeste
        dagene.  Det viktige er at du oppretter kontakt så gjesten vet at $han_hun er innvitert på middag.</p>\n";
        $text .= "<p>Dersom du har noen spørsmål ta gjerne kontakt med oss eller se ".
        '<a href="http://www.kom-inn.org/">http://www.kom-inn.org/</a>' . " for litt mer informasjon!</p>\n".
        "<p>Takk igjen, lykke til og send oss gjerne et bilde hvis det føles naturlig - eller del det på Facebook-siden vår.</p>\n".
        "<p>Med vennlig hilsen<br> Helle, Lars, Johan</p>";
        return $text;
    }

    /**
     * Build feedback request text
     *
     * @param $match
     * @return string
     */
    protected function buildFeedbackRequestText($match) : string
    {
        $id   = $match['id'];
        $code = $this->createHashCode($match['host']['email']);
        $base = Environment::get('base_url');
        $url  = "{$base}/feedback/{$id}/{$code}";
        $yes  = $url . '/yes';
        $no   = $url . '/no';

        $text = "<h1>Hei igjen!</h1>";
        $text .= "<p>Takk for at du meldte deg på Kom Inn!</p>";
        $text .= "<p>Det er nå en liten stund siden du fikk tildelt din middagsgjest.</p>";
        $text .= "<p>Vi håper at matchen var vellykket!</p>";
        $text .= "<p>Vi vil gjerne vite om dere har gjennomført middagen - eller laget en helt konkret avtale med gjesten. </p>";
        $text .= "<p><a href={$yes}>Trykk her</a> – hvis dere har gjennomført middagen eller laget en helt konkret avtale</p>";
        $text .= "<p><a href={$no}>Trykk her</a> – hvis dere av ulike årsaker ikke kommer til å gjennomføre middagen allikevel.</p>";
        $text .= "<p>Er det noe mer du vil fortelle? Har du spørsmål, tilbakemeldinger –  eller kanskje et bilde fra middagen? Send oss gjerne en mail på kominnoslo@gmail.com</p>";
        $text .= "<p>Med vennlig hilsen oss i Kom Inn:)</p>";

        return $text;
    }
}
