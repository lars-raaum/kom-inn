<?php

namespace app\mails;

use app\Mailer;

class Sorry
{
    CONST SORRY_GUEST   = 'SORRY GUEST';
    CONST SORRY_HOST    = 'SORRY HOST';
    CONST SORRY_NEUTRAL = 'SORRY NEUTRAL';

    protected $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Build text to use in SorryHost mail
     *
     * @param array $person
     * @return string
     */
    public function buildSorryHostText(array $person) : string
    {
        return $this->buildSorryNeutralText($person);
    }

    /**
     * @param array $person
     * @return string
     */
    public function buildSorryGuestText(array $person) : string
    {
        return $this->buildSorryNeutralText($person);
    }

    /**
     * @param array $person
     * @return string
     */
    public function buildSorryNeutralText(array $person) : string
    {
        $name = $person['name'];
        $text = "<h1>Hei {$name}</h1>\n\n
    <p>Først og fremst takk for at du vil være med på Kom inn!</p>\n
    <p>Vi har desverre ikke funnet noen match for deg i ditt område.</p>\n";
        $text .= "<p>Dersom du har noen spørsmål ta gjerne kontakt med oss eller se ".
            '<a href="http://www.kom-inn.org/">http://www.kom-inn.org/</a>' . " for litt mer informasjon!</p>\n".
            "<p>Takk igjen.</p>\n".
            "<p>Med vennlig hilsen<br> Helle, Lars, Johan</p>";
        return $text;
    }
}
