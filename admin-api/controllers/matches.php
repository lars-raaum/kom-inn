<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('/match/{id}', function ($id) use ($app) {
    $match = $app['matches']->get((int) $id);
    if (!$match) return $app->json(null, 404, ['X-Error-Message' => "Match $id not found!"]);
    return $app->json($match);
});

$app->get('/matches', function(Request $request) use ($app) {
    $status = isset($_GET['status']) ? (int) $_GET['status'] : 0;

    // @TODO add pagination
    $matches = $app['matches']->find($status);
    return $app->json($matches);
});

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
    error_log("INSERT match Guest[{$data['guest_id']}] Host[{$data['host_id']}] by [{$app['PHP_AUTH_USER']}]");
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

    $match = $app['matches']->get($id);
    if (!$match) {
        return $app->json(['result' => false]);
    }

    $sms_sender = new \app\Sms();
    $result = $sms_sender->sendHostInform($match);

    $email_sender = new \app\Emailing();
    $result = $email_sender->sendHostInform($match);

    return $app->json(['result' => true]);
});

$app->post('/match/{id}', function ($id, Request $request) use ($app) {

    $id = (int) $id;
    $match = $app['matches']->get($id, false, false);
    if (!$match) {
        return $app->json(null, 404, ['X-Error-Message' => "Match $id not found"]);
    }

    $r = $request->request;
    $data  = [
        'status'  => $r->get('status'),
        'comment' => $r->get('comment')
    ];
    $saved = $app['matches']->update($id, $data);
    if (!$saved) {
        return $app->json(null, 500, ['X-Error-Message' => "Unable to update Match $id"]);
    }

    $match = $app['matches']->get($id);
    return $app->json($match);
});

$app->delete('/match/{id}', function ($id, Request $request) use ($app) {
    $args = [(int) $id];
    $sql = "SELECT * FROM matches WHERE id = ?";
    error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$app['PHP_AUTH_USER']}]");
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
    error_log("Soft delete Match[{$id}] by [{$app['PHP_AUTH_USER']}]");
    $result = $app['db']->update('matches', $data, ['id' => (int) $id], $types);
    if (!$result) {
        error_log("Failed to update match {$id}");
        return $app->json(null, 500);
    }
    $guest_id = $match['guest_id'];
    $host_id  = $match['host_id'];

    $data = ['status' => 1, 'updated' => $now->format('Y-m-d H:i:s')];
    error_log("Set Person[{$guest_id}] to used by [{$app['PHP_AUTH_USER']}]");
    $result = $app['db']->update('people', $data, ['id' => $guest_id]);
    if (!$result) {
        error_log("Failed to updated person {$guest_id} to be used!");
        return $app->json(['result' => false]);
    }

    error_log("Set Person[{$host_id}] to used by [{$app['PHP_AUTH_USER']}]");
    $result = $app['db']->update('people', $data, ['id' => $host_id]);
    if (!$result) {
        error_log("Failed to updated person {$host_id} to be used!");
        return $app->json(['result' => false]);
    }

    $match = $app['matches']->get($id);
    if (!$match) {
        return $app->json(null, 500);
    }

    return $app->json($match);

});
