<?php

namespace app\mails;

use app\Environment;
use app\Mailer;

class Purge
{
    CONST EXPIRED_HOST = 'EXPIRED HOST';
    CONST REACTIVATE_PERSON = 'REACTIVATE PERSON';

    protected $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Build text to use in HostInform mail
     *
     * @param array $person
     * @return string
     */
    public function buildReactivateUsedText(array $person) : string
    {
        $name = $person['name'];
        $url  = $this->reactiveUrl($person);
        $text = "<h1>Hei {$name}</h1>\n\n
        <p>For en stund siden meldte du deg på Kom inn - Lær norsk rundt middagsbordet. Vi håper du har vært på én eller flere hyggelige middager!</p>\n
        <p>Hvis du vil bli delta på en ny middag med en annen familie, <a href=\"{$url}\">klikk på denne lenken!</a></p>\n
        <p>Hvis du aldri har vært på middag, men fortsatt har lyst,  <a href=\"{$url}\">klikk på denne linken.</a></p>
        <p>Kom inn drives på fritiden av en liten gruppe frivillige og vi rekrutterer gjester gjennom å dra på skolebesøk til Voksenopplæringene i Oslo.
        Dette gjør vi noen ganger i halvåret, så det kan ta noe tid før vi finner en match. Vi finner heller ikke matcher til alle, men jo flere vi har
        som ønsker å invitere, jo lettere er det å skape gode matcher!</p>
        <p>mvh<br>\n
        Kom inn<br>\n
        " . '<a href="http://www.kom-inn.org/">http://www.kom-inn.org/</a>' . "<br>\n
        " . '<a href="https://www.facebook.com/kominnnorge">https://www.facebook.com/kominnnorge</a>' . "</p>";
        return $text;
    }

    /**
     * Build text to use in HostInform mail
     *
     * @param array $host
     * @return string
     */
    public function buildExpiredHostText(array $host) : string
    {
        $name = $host['name'];
        $url  = $this->reactiveUrl($host);
        $text = "<h1>Hei {$name}</h1>\n\n
        <p>For en stund siden meldte du deg på Kom inn - Lær norsk rundt middagsbordet. Takk for det!</p>\n
        <p>Vi har dessverre ikke funnet match til deg i løpet av denne tiden, men vi håper du fortsatt kunne tenke deg å invitere på middag!</p>\n
        <p>Da trenger du bare å <a href=\"{$url}\">klikke på denne lenken!</a></p>\n
        <p>Hvis du ikke trykker på linken antar vi at du ikke lenger er interessert og vi vil slette deg.</p>\n
        <p>Kom inn drives på fritiden av en liten gruppe frivillige og vi rekrutterer gjester gjennom å dra på skolebesøk til Voksenopplæringene i Oslo.</p>\n
        <p>Hvert semester har vi flere titalls skolebesøk og matcher flere hundre middager. Vi rekrutterer hele tiden nye gjester og vil ha behov for nye verter. Jo flere verter vi har, jo større sjanse for gode matcher!</p>\n
        <p>Som sagt: Dersom du fortsatt kunne tenke deg å invitere på middag, <a href=\"{$url}\">klikk på denne linken.</a> Dersom du ikke lenger er interessert, trenger du ikke å gjøre noe.</p>\n
        <p>mvh<br>\n
        Kom inn<br>\n
        " . '<a href="http://www.kom-inn.org/">http://www.kom-inn.org/</a>' . "<br>\n
        " . '<a href="https://www.facebook.com/kominnnorge">https://www.facebook.com/kominnnorge</a>' . "</p>";
        return $text;
    }
    private function reactiveUrl(array $host) : string
    {
        $id   = $host['id'];
        $code = $this->mailer->createHashCode($host['email']);
        $base = Environment::get('base_url');
        return "{$base}/reactivate/{$id}/{$code}";
    }
}
/**
EMAIL TEXT:
From name: Kom inn - Lær norsk rundt middagsbordet
From email: kominnoslo@gmail.com
Subject:
*/