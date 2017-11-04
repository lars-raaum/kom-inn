<?php

namespace app\mails;

use app\Environment;
use app\Mailer;
use app\models\People;

class Purge
{
    CONST EXPIRED_HOST = 'EXPIRED HOST';
    CONST EXPIRED_GUEST = 'EXPIRED GUEST';
    CONST REACTIVATE_HOST = 'REACTIVATE HOST';
    CONST REACTIVATE_GUEST = 'REACTIVATE GUEST';

    protected $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Build text to use in HostInform mail
     *
     * @param array $host
     * @return string
     */
    public function buildReactivateUsedHostText(array $host) : string
    {
        $name = $host['name'];
        $url  = $this->reactiveUrl($host);
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
     * Build text to use in GuestReactivate mail
     *
     * @param array $guest
     * @return string
     */
    public function buildReactivateUsedGuestText(array $guest) : string
    {
        $name = $guest['name'];
        $url  = $this->reactiveUrl($guest);
        $text = "<h1>Hei {$name}</h1>\n\n
        <p>For en stund siden meldte du deg på Kom inn - Lær norsk rundt middagsbordet. Vi håper du har vært på én eller flere hyggelige middager!</p>\n
        <p>Hvis du vil bli delta på en ny middag med en annen familie, <a href=\"{$url}\">klikk på denne lenken!</a></p>\n
        <p>Hvis du aldri har vært på middag, men fortsatt har lyst,  <a href=\"{$url}\">klikk på denne linken.</a></p>
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
    private function reactiveUrl(array $person) : string
    {
        $id   = $person['id'];
        $code = $this->mailer->createHashCode($person['email']);
        $base = Environment::get('base_url');
        $type = $person['type'] == People::TYPE_GUEST ? 'gjest' : 'vert';
        return "{$base}/reactivate/{$id}/{$code}/{$type}";
    }

    /**
     * Build text to use in GuestExpired mail
     *
     * @param array $guest
     * @return string
     */
    public function buildExpiredGuestText(array $guest) : string
    {
        $name = $guest['name'];
        $url  = $this->reactiveUrl($guest);
        $text = "<h1>Hei {$name}</h1>\n\n
        <p>For en stund siden meldte du deg på Kom inn - Lær norsk rundt middagsbordet. Takk for det!</p>\n
        <p>Vi har dessverre ikke funnet match til deg i løpet av denne tiden, men vi håper du fortsatt kunne tenke deg å delta på middag!</p>\n
        <p>Da trenger du bare å <a href=\"{$url}\">klikke på denne lenken!</a></p>\n
        <p>Hvis du ikke trykker på linken antar vi at du ikke lenger er interessert og vi vil slette deg.</p>\n
        <p>mvh<br>\n
        Kom inn<br>\n
        " . '<a href="http://www.kom-inn.org/">http://www.kom-inn.org/</a>' . "<br>\n
        " . '<a href="https://www.facebook.com/kominnnorge">https://www.facebook.com/kominnnorge</a>' . "</p>";
        return $text;
    }

}
/**
EMAIL TEXT:
From name: Kom inn - Lær norsk rundt middagsbordet
From email: kominnoslo@gmail.com
Subject:
*/