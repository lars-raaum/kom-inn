<?php

use Symfony\Component\HttpFoundation\Request;
use Mailgun\Mailgun;


function getMatch($id, $app) {

    $sql = "SELECT * FROM matches WHERE id = ?";
    $match = $app['db']->fetchAssoc($sql, [(int) $id]);
    if (!$match) {
        return false;
    }

    $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
    $match['host'] = $app['db']->fetchAssoc($sql, [(int) $match['host_id']]);

    $sql = "SELECT people.*, guests.food_concerns FROM people, guests WHERE people.id = guests.user_id AND people.id = ?";
    $match['guest'] = $app['db']->fetchAssoc($sql, [(int) $match['guest_id']]);

    return $match;
}

$app->post('/match', function(Request $request) use ($app, $types, $dtt) {
    $r = $request->request;
    $guest_id = $r->get('guest_id');
    $host_id  = $r->get('host_id');
    $now      = new DateTime('now');
    $data = [
        'guest_id' => $guest_id,
        'host_id'  => $host_id,
        'comment'  => $r->get('comment'),
        'updated'  => $now,
        'created'  => $now
    ];
    $result = $app['db']->insert('matches', $data, $types);
    if (!$result) {
        return $app->json(['result' => false]);
    }
    $id = $app['db']->lastInsertId();

    $data = ['status' => 2, 'updated' => $now->format('Y-m-d H:i:s')];
    $result = $app['db']->update('people', $data, ['id' => $guest_id]);
    if (!$result) {
        error_log("Failed to updated person {$guest_id} to be used!");
        return $app->json(['result' => false]);
    }
    $result = $app['db']->update('people', $data, ['id' => $host_id]);
    if (!$result) {
        error_log("Failed to updated person {$host_id} to be used!");
        return $app->json(['result' => false]);
    }


    $match = getMatch($id, $app);
    if (!$match) {
        return $app->json(['result' => false]);
    }

    $sender = new \app\Sms();
    $result = $sender->sendHostInform($match);

    return $app->json(['result' => true]);
});

$app->get('/match/{id}', function ($id) use ($app) {

    $match = getMatch($id, $app);
    if (!$match) return $app->json(null, 404);
    return $app->json($match);
});

$app->post('/match/{id}', function ($id, Request $request) use ($app) {

    $sql = "SELECT * FROM matches WHERE id = ?";
    $match = $app['db']->fetchAssoc($sql, [(int) $id]);
    if (!$match) {
        return $app->json(null, 404);
    }

    $r = $request->request;
    $types = ['updated' => \Doctrine\DBAL\Types\Type::getType('datetime')];
    $data  = [
        'status'  => $r->get('status'),
        'comment' => $r->get('comment'),
        'updated' => new DateTime('now')
    ];
    $result = $app['db']->update('matches', $data, ['id' => (int) $id], $types);
    if (!$result) {
        error_log("Failed to update match {$id}");
        return $app->json(null, 500);
    }

    # First, instantiate the SDK with your API credentials and define your domain.
    $mg = new Mailgun("key-4d699703721ad54fe248b4ed18da8526");
    $domain = "naggingnelly.com";

    # Now, compose and send your message.
    $mg->sendMessage($domain, array('from'    => 'kom-inn@naggingnelly.com',
        'to'      =>  'techfugees@jlf.me',
        'subject' => 'Kom inn: Gjester venter på invitasjon :)',
        'text'    => "Hei $name (Or $name remove everything after first space)\n\nFørst og fremst takk for at dere vil være med på Kom inn!\n\nVi håper dere er klare for middag:)\n\nHva skjer nå?\n\nVi har funnet gjester som gjerne vil komme på besøk. $guestname på $age fra $origin\n\nIf SUM(adults_m + adults_f + children)=0 $genderpronoun kommer alene. else $genderpronoun tar med seg $adults_m voksne menn, $adults_f voksne kvinner, og $children barn.\n\nPå spørsmål om det er noe $genderpronoun ikke spiser, svarer $genderpronoun: $food_concerns\n\nSelve invitasjonen står du for selv slik at eventuell videre kommunikasjonen kan skje direkte. Vi anbefaler at du bruker SMS. Skriv meldingen på norsk, nevn gjerne \"Kom inn\" og husk å få med tidspunkt/adresse.\n\nDersom du ikke hører noe på SMS, så anbefaler vi at du tar en telefon. Noen er komfortable med å snakke norsk, men synes skriftlig er vanskelig.\n\nTelefonnummeret til $guestname er $guestphonenumber\n\nVi opplever dessverre at en god del verter ikke sender en invitasjon, så vi ber deg invitere i løpet av 48 timer og gi oss beskjed. Selve avtalen trenger ikke å være i løpet av de nærmeste dagene, det viktige er at dere oppretter kontakten.\n\nDersom du har noen spørsmål ta gjerne kontakt med oss eller se http://www.kom-inn.org/ for litt mer praktisk informasjon!\n\nTakk igjen, lykke til og send oss gjerne et bilde hvis det føles naturlig - eller del det på Facebook-siden vår.\n\nMed vennlig hilsen Kom inn-teamet!"));

    $sql = "SELECT * FROM matches WHERE id = ?";
    $match = $app['db']->fetchAssoc($sql, [(int) $id]);

    $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
    $match['host'] = $app['db']->fetchAssoc($sql, [(int) $match['host_id']]);

    $sql = "SELECT people.*, guests.food_concerns FROM people, guests WHERE people.id = guests.user_id AND people.id = ?";
    $match['guest'] = $app['db']->fetchAssoc($sql, [(int) $match['guest_id']]);

    return $app->json($match);
});


$app->get('/matches', function(Request $request) use ($app) {
    $status = 0; // matched
    $sql = "SELECT * FROM matches WHERE status = ?";
    $matches = $app['db']->fetchAll($sql, [(int) $status]);
    foreach ($matches as $k => $match) {

        $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
        $matches[$k]['host'] = $app['db']->fetchAssoc($sql, [(int) $match['host_id']]);

        $sql = "SELECT people.*, guests.food_concerns FROM people, guests WHERE people.id = guests.user_id AND people.id = ?";
        $matches[$k]['guest'] = $app['db']->fetchAssoc($sql, [(int) $match['guest_id']]);
    }
    return $app->json($matches);
});
