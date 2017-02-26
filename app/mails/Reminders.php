<?php

namespace app\mails;

use app\Mailer;
use app\Environment;

class Reminders
{
    const NEUTRAL = 'NEUTRAL';
    const FIRST = 'FIRST';
    const SECOND = 'SECOND';
    const THIRD = 'THIRD';

    public static $TYPES = [self::NEUTRAL, self::FIRST, self::SECOND, self::THIRD];
    protected $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Build feedback request text
     *
     * @param array $match
     * @return string
     */
    public function buildFeedbackRequestText2days(array $match) : string
    {
        list($yes, $no) = $this->reminderUrls($match);
        return <<<EOT
<h1>Hei!</h1>
<p>Takk for at du har meldt deg på Kom Inn:)</p>
<p>Vi sendte deg en mail for et par dager siden hvor du fikk kontaktinformasjon til din middagsgjest.</p>
<p>Har dere vært i kontakt og laget en helt konkret middagsavtale?</p>
<p>Hvis ja – flott!</p>
<p>Hvis ikke du har vært i kontakt med gjesten, er det fint om du sender en sms eller ringer han eller
henne i løpet av kort tid og lager en konkret avtale.</p>
<p>Vi vil gjerne at gjesten skal slippe å vente lenge med å få en invitasjon. Om du ikke har anledning til
å invitere til middag i umiddelbar framtid, er det greit å lage en konkret avtale når du har tid.</p>

<p><a href="{$yes}">Trykk her hvis/når du har gjennomført middag eller laget en konkret avtale.</a></p>
<p><a href="{$no}">Trykk her hvis middagen av en eller annen grunn ikke blir noe av allikevel.</a></p>

<p>Har du spørsmål, noe mer du vil fortelle eller kanskje etter hvert et bilde fra kvelden? Send oss en mail til
<a href="mailto:kominnoslo@gmail.com">kominnoslo@gmail.com</a></p>';
<p>Vennlig hilsen oss i Kom Inn</p>
EOT;
    }

    /**
     * Build feedback request text
     *
     * @param array $match
     * @return string
     */
    public function buildFeedbackRequestText4days(array $match) : string
    {
        list($yes, $no) = $this->reminderUrls($match);
        return <<<EOT
<h1>Hei!</h1>
<p>Takk for at du har meldt deg på Kom Inn:)</p>
<p>Det er nå gått fire dager siden du mottok mail med kontaktinformasjon til din middagsgjest.</p>
<p>Kanskje du allerede har laget en konkret middagsavtale eller til og med gjennomført en middag?</p>
<p>Hvis ikke du har invitert din middagsgjest ennå, er det fint om du nå gjør dette umiddelbart.</p>
<p>Det er viktig for oss i Kom Inn at gjesten blir invitert før det går for lang tid. Om du ikke har
anledning til å invitere til middag i nær framtid, er det også greit å lage en konkret avtale litt frem i tid.</p>

<p><a href="{$yes}">Trykk her hvis/når du har gjennomført middag eller laget en konkret avtale.</a></p>
<p><a href="{$no}">Trykk her hvis middagen av en eller annen grunn ikke blir noe av allikevel.</a></p>

<p>Har du spørsmål, noe mer du vil fortelle eller kanskje etter hvert et bilde fra kvelden? Send oss en mail til
<a href="mailto:kominnoslo@gmail.com">kominnoslo@gmail.com</a></p>';
<p>Vennlig hilsen oss i Kom Inn</p>
EOT;
    }

    /**
     * Build feedback request text
     *
     * @param array $match
     * @return string
     */
    public function buildFeedbackRequestText7days(array $match) : string
    {
        list($yes, $no) = $this->reminderUrls($match);
        return <<<EOT
<h1>Hei!</h1>
<p>Takk for at du har meldt deg på Kom Inn:)</p>
<p>For en uke siden fikk du en mail med kontaktinformasjon til din middagsgjest.</p>
<p>Har du laget en konkret avtale med din middagsgjest eller kanskje allerede gjennomført en middag?</p>
<p>Om du ikke har gjort det, er det fint om du ringer eller sender en sms umiddelbart. Det er viktig at
gjesten som venter, vet at han/henne blir invitert. Om du ikke har anledning til å invitere til middag
i umiddelbar framtid, er det også helt greit å lage en konkret avtale litt frem i tid.</p>
<p>Dette er siste purremail du får fra oss. Om vi ikke hører noe fra deg i løpet av 48 timer – finner
vi en ny match til gjesten og sletter dine data fra listene våre.</p>
<p>Vi håper du inviterer til middag og at dere får en hyggelig kveld!</p>

<p><a href="{$yes}">Trykk her hvis/når du har gjennomført middag eller laget en konkret avtale.</a></p>
<p><a href="{$no}">Trykk her hvis middagen av en eller annen grunn ikke blir noe av allikevel.</a></p>

<p>Har du spørsmål, noe mer du vil fortelle eller kanskje etter hvert et bilde fra kvelden? Send oss en mail til
<a href="mailto:kominnoslo@gmail.com">kominnoslo@gmail.com</a></p>';
<p>Vennlig hilsen oss i Kom Inn</p>
EOT;
    }

    /**
     * Build feedback request text
     *
     * @param array $match
     * @return string
     */
    public function buildFeedbackRequestText(array $match) : string
    {
        list($yes, $no) = $this->reminderUrls($match);
        return <<<EOT
<h1>Hei igjen!</h1>";
<p>Takk for at du meldte deg på Kom Inn!</p>
<p>Det er nå en liten stund siden du fikk tildelt din middagsgjest.</p>
<p>Vi håper at matchen var vellykket!</p>
<p>Vi vil gjerne vite om dere har gjennomført middagen - eller laget en helt konkret avtale med gjesten. </p>
<p><a href="{$yes}">Trykk her</a> – hvis dere har gjennomført middagen eller laget en helt konkret avtale</p>
<p><a href="{$no}">Trykk her</a> – hvis dere av ulike årsaker ikke kommer til å gjennomføre middagen allikevel.</p>
<p>Er det noe mer du vil fortelle? Har du spørsmål, tilbakemeldinger –  eller kanskje et bilde fra middagen? Send oss gjerne en mail på
<a href="mailto:kominnoslo@gmail.com">kominnoslo@gmail.com</a></p>
<p>Med vennlig hilsen oss i Kom Inn:)</p>
EOT;
    }

    private function reminderUrls(array $match) : array
    {
        $id   = $match['id'];
        $code = $this->mailer->createHashCode($match['host']['email']);
        $base = Environment::get('base_url');
        $url  = "{$base}/feedback/{$id}/{$code}";
        $yes  = $url . '/yes';
        $no   = $url . '/no';
        return [$yes, $no];
    }
}