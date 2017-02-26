<?php

namespace app\mails;

use app\Mailer;

class HostInform
{
    CONST HOST_INFORM = 'HOST INFORM';

    protected $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Build text to use in HostInform mail
     *
     * @param array $match
     * @return string
     */
    public function buildHostInformText(array $match) : string
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
}
