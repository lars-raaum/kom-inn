<?php

use Symfony\Component\HttpFoundation\Request;
use app\models\Matches;

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

// @TODO move to model and add transactions / validations
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
        return $app->json(['result' => false], 500, ['X-Error-Message' => 'Failed to insert match']);
    }
    $id = $app['db']->lastInsertId();

    $data = ['status' => 2, 'updated' => $now->format('Y-m-d H:i:s')];
    $result = $app['db']->update('people', $data, ['id' => $guest_id]);
    if (!$result) {
        error_log("Failed to updated person {$guest_id} to be used!");
        return $app->json(['result' => false], 500, ['X-Error-Message' => 'Failed to update guest']);
    }
    $result = $app['db']->update('people', $data, ['id' => $host_id]);
    if (!$result) {
        error_log("Failed to updated person {$host_id} to be used!");
        return $app->json(['result' => false], 500, ['X-Error-Message' => 'Failed to update host']);
    }

    $match = $app['matches']->get($id);
    if (!$match) {
        return $app->json(['result' => false], 500, ['X-Error-Message' => 'Inserted match not found!']);
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
    $match = $app['matches']->get((int) $id, false, false);
    if (!$match) {
        return $app->json(null, 404, ['X-Error-Message' => "Match $id not found"]);
    }
    if ($match['status'] == Matches::STATUS_DELETED) {
        return $app->json(null, 400, ['X-Error-Message' => "Match $id is already deleted"]);
    }

    $result = $app['matches']->delete($match['id']);
    if (!$result) {
        return $app->json(null, 500, ['X-Error-Message' => "Could not delete match $id"]);
    }

    $result = $app['people']->setToActive($match['guest_id']);
    if (!$result) {
        error_log("Failed to updated person {$match['guest_id']} to be used!");
        return $app->json(null, 500, ['X-Error-Message' => "Failed to updated person {$match['guest_id']} to be used!"]);
    }
    $result = $app['people']->setToActive($match['host_id']);
    if (!$result) {
        error_log("Failed to updated person {$match['host_id']} to be used!");
        return $app->json(null, 500, ['X-Error-Message' => "Failed to updated person {$match['host_id']} to be used!"]);
    }

    return $app->json(['result' => true]);

});
