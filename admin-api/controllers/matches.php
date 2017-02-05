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
$app->post('/match', function(Request $request) use ($app) {
    $r = $request->request;
    $guest_id = $r->get('guest_id');
    $host_id  = $r->get('host_id');

    #validation
    if (empty($guest_id) || empty($host_id)) {
        error_log(print_r(compact('guest_id', 'host_id'), true));
        return $app->json(null, 400, ['X-Error-Message' => 'Missing required fields guest_id and host_id']);
    }

    $data = [
        'guest_id' => $guest_id,
        'host_id'  => $host_id,
        'comment'  => $r->get('comment'),
    ];
    $id = $app['matches']->insert($data);
    if ($id === false) {
        return $app->json(null, 500, ['X-Error-Message' => 'Failed to insert match']);
    }

    $result = $app['people']->setToUsed($guest_id);
    if (!$result) {
        error_log("ERROR: Failed to updated person {$guest_id} to be used!");
        return $app->json(null, 500, ['X-Error-Message' => 'Failed to update guest']);
    }

    $result = $app['people']->setToUsed($host_id);
    if (!$result) {
        error_log("Failed to updated person {$host_id} to be used!");
        return $app->json(null, 500, ['X-Error-Message' => 'Failed to update host']);
    }

    $match = $app['matches']->get($id);
    if (!$match) {
        return $app->json(null, 500, ['X-Error-Message' => 'Inserted match not found!']);
    }

    $result = $app['sms']->sendHostInform($match);
    $result = $app['email']->sendHostInform($match);

    return $app->json($match);
});

$app->post('/match/{id}', function ($id, Request $request) use ($app) {

    $id = (int) $id;
    $match = $app['matches']->get($id, false, false);
    if (!$match) {
        return $app->json(null, 404, ['X-Error-Message' => "Match $id not found"]);
    }

    $r = $request->request;

    $status = $r->get('status');
    $comment = $r->get('comment');
    $data  = [];
    if ($comment && $comment !== $match['comment']) {
        $data['comment'] = $comment;
    }
    if ($status && $status !== $match['status']) {
        $data['status'] = $status;
    }
    if (empty($data)) {
        return $app->json(null, 400, ['X-Error-Message' => 'Nothing to save']);
    }

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
