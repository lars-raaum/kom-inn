<?php

namespace app;

use Mailgun\Mailgun;

class Emailing {

    protected $client;
    protected $domain;
    protected $admin;

    public function __construct() {
        $config = require_once RESOURCE_PATH . '/emails.php';
        if (empty($config)) return;

        $this->client = new Mailgun($config['key']);
        $this->domain = $config['domain'];
        $this->from   = $config['from'];
        $this->admin = isset($config['admin']) ? $config['admin'] : false;
    }

    public function sendAdminRegistrationNotice() {
        if (empty($this->client) || empty($this->admin)) return;
        $this->client->sendMessage($this->domain, [
            'from'    => $this->from,
            'to'      => $this->admin,
            'subject' => 'Kom inn: Ny gjest',
            'html'    => '<h1>Ny gjest</h1><p><a href="http://kom-inn.org/admin">Finn match</a></p>'
        ]);
    }

    public function sendHostInform(array $match) {
        if (empty($this->client)) return;
        $to = $match['host']['email'];
        try {
            $this->client->sendMessage($this->domain, [
                'from'    => $this->from,
                'to'      => $to,
                'subject' => 'Kom inn: Gjester venter på invitasjon :)',
                'html'    => $this->buildText($match)
            ]);
        } catch (\Exception $e) {
            error_log("Failed to mail : " . $e->getMessage());
            return false;
        }
    }

    protected function buildText(array $match) {
        $name       = $match['host']['name'];
        $guestname  = $match['guest']['name'];
        $age        = $match['guest']['age'];
        $origin     = $match['guest']['origin'];


        $text = "<h1>Hei {$name}</h1>\n\n
                <p>Først og fremst takk for at dere vil være med på Kom inn!</p>\n
                <p>Vi håper dere er klare for middag :)</p>\n
                <p>Hva skjer nå?<p>\n
                <p>Vi har funnet gjester som gjerne vil komme på besøk. $guestname på $age fra $origin</p>\n";

        $g = $match['guest']['gender'];
        $gender = $g == 'MALE' ? "Han" : ( $g == 'FEMALE' ? 'Hun' : 'Dem' );
        if ($match['guest']['adults_m'] + $match['guest']['adults_f'] + $match['guest']['children'] == 1) {
            $text .= "<p>$gender kommer alene.</p>";
        } else {
            $adults_m = $g == 'MALE' ? $match['guest']['adults_m']-1 : $match['guest']['adults_m'];
            $adults_f = $g == 'FEMALE' ? $match['guest']['adults_f']-1 : $match['guest']['adults_f'];
            $children = $match['guest']['children'];
            $text .= "<p>$gender tar med seg {$adults_m} voksne menn, {$adults_f} voksne kvinner, og {$children} barn.</p>\n";
        }
        $food_concerns      = $match['guest']['food_concerns'];
        if ($food_concerns) {
            $text .= "<p>På spørsmål om det er noe $gender ikke spiser, svarer $gender: {$food_concerns}</p>\n";
        }
        $guestphonenumber   = $match['guest']['phone'];
        $text .= "<p>Selve invitasjonen står du for selv slik at eventuell videre kommunikasjonen kan skje direkte. Vi anbefaler at du bruker SMS. Skriv meldingen på norsk, nevn gjerne \"Kom inn\" og husk å få med tidspunkt/adresse.</p>\n
                <p>Dersom du ikke hører noe på SMS, så anbefaler vi at du tar en telefon. Noen er komfortable med å snakke norsk, men synes skriftlig er vanskelig.</p>\n
                <p>Telefonnummeret til {$guestname} er {$guestphonenumber}</p>\n
                <p>Vi opplever dessverre at en god del verter ikke sender en invitasjon, så vi ber deg invitere i løpet av 48 timer og gi oss beskjed. Selve avtalen trenger ikke å være i løpet av de nærmeste dagene, det viktige er at dere oppretter kontakten.</p>\n
                <p>Dersom du har noen spørsmål ta gjerne kontakt med oss eller se http://www.kom-inn.org/ for litt mer praktisk informasjon!</p>\n
                <p>Takk igjen, lykke til og send oss gjerne et bilde hvis det føles naturlig - eller del det på Facebook-siden vår.</p>\n
                <p>Med vennlig hilsen<br> Kom inn-teamet!</p>";
        return $text;
    }
}
