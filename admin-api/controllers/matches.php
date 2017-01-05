<?php

use Symfony\Component\HttpFoundation\Request;


function getMatch($id, $app) {

    $args = [(int) $id];
    $sql = "SELECT * FROM matches WHERE id = ?";
    error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
    $match = $app['db']->fetchAssoc($sql, $args);
    if (!$match) {
        return false;
    }

    $args = [(int) $match['host_id']];
    $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
    error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
    $match['host'] = $app['db']->fetchAssoc($sql, $args);

    $args = [(int) $match['guest_id']];
    $sql = "SELECT people.*, guests.food_concerns FROM people, guests WHERE people.id = guests.user_id AND people.id = ?";
    error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
    $match['guest'] = $app['db']->fetchAssoc($sql, $args);

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
    error_log("INSERT match Guest[{$data['guest_id']}] Host[{$data['host_id']}] by [{$_SERVER['PHP_AUTH_USER']}]");
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

    $sms_sender = new \app\Sms();
    $result = $sms_sender->sendHostInform($match);

    $email_sender = new \app\Emailing();
    $result = $email_sender->sendHostInform($match);

    return $app->json(['result' => true]);
});

$app->get('/match/{id}', function ($id) use ($app) {
    $match = getMatch($id, $app);
    if (!$match) return $app->json(null, 404);
    return $app->json($match);
});

$app->post('/match/{id}', function ($id, Request $request) use ($app) {

    $args = [(int) $id];
    $sql = "SELECT * FROM matches WHERE id = ?";
    error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
    $match = $app['db']->fetchAssoc($sql, $args);
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

    error_log("Update Match[{$id}] by [{$_SERVER['PHP_AUTH_USER']}]");
    $result = $app['db']->update('matches', $data, ['id' => (int) $id], $types);
    if (!$result) {
        error_log("Failed to update match {$id}");
        return $app->json(null, 500);
    }

    $match = getMatch($id, $app);
    if (!$match) {
        return $app->json(null, 500);
    }

    return $app->json($match);
});

$app->delete('/match/{id}', function ($id, Request $request) use ($app) {
    $args = [(int) $id];
    $sql = "SELECT * FROM matches WHERE id = ?";
    error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
    $match = $app['db']->fetchAssoc($sql, $args);
    if (!$match) {
        return $app->json(null, 404);
    }

    $r     = $request->request;
    $now   = new DateTime('now');
    $types = ['updated' => \Doctrine\DBAL\Types\Type::getType('datetime')];
    $data  = [
        'status'  => -1,
        'updated' => new DateTime('now')
    ];
    error_log("Soft delete Match[{$id}] by [{$_SERVER['PHP_AUTH_USER']}]");
    $result = $app['db']->update('matches', $data, ['id' => (int) $id], $types);
    if (!$result) {
        error_log("Failed to update match {$id}");
        return $app->json(null, 500);
    }
    $guest_id = $match['guest_id'];
    $host_id  = $match['host_id'];

    $data = ['status' => 1, 'updated' => $now->format('Y-m-d H:i:s')];
    error_log("Set Person[{$guest_id}] to used by [{$_SERVER['PHP_AUTH_USER']}]");
    $result = $app['db']->update('people', $data, ['id' => $guest_id]);
    if (!$result) {
        error_log("Failed to updated person {$guest_id} to be used!");
        return $app->json(['result' => false]);
    }

    error_log("Set Person[{$host_id}] to used by [{$_SERVER['PHP_AUTH_USER']}]");
    $result = $app['db']->update('people', $data, ['id' => $host_id]);
    if (!$result) {
        error_log("Failed to updated person {$host_id} to be used!");
        return $app->json(['result' => false]);
    }

    $match = getMatch($id, $app);
    if (!$match) {
        return $app->json(null, 500);
    }

    return $app->json($match);

});

$app->get('/matches', function(Request $request) use ($app) {
    $status = isset($_GET['status']) ? $_GET['status'] : 0;

    $args = [(int) $status];

    // TODO join requests
    $sql = "SELECT * FROM matches WHERE status = ? ORDER BY id DESC";
    error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
    $matches = $app['db']->fetchAll($sql, $args);

    $hosts_sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
    error_log("SQL [ $hosts_sql ] [x] foreach match - by [{$_SERVER['PHP_AUTH_USER']}]");

    $guests_sql = "SELECT people.*, guests.food_concerns FROM people, guests WHERE people.id = guests.user_id AND people.id = ?";
    error_log("SQL [ $guests_sql ] [x] foreach match - by [{$_SERVER['PHP_AUTH_USER']}]");

    foreach ($matches as $k => $match) {
        $matches[$k]['host'] = $app['db']->fetchAssoc($hosts_sql, [(int) $match['host_id']]);
        $matches[$k]['guest'] = $app['db']->fetchAssoc($guests_sql, [(int) $match['guest_id']]);
    }
    return $app->json($matches);
});
