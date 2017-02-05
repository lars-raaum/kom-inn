<?php

use Symfony\Component\HttpFoundation\Request;
use app\models\People;

$app->post('/reactivate', function(Request $request) use ($app) {
    $r = $request->request;

    $id   = (int) $r->get('id');
    $code = $r->get('code');

    if (empty($id) || empty($code)) {
        return $app->json(null, 400, ['X-Error-Message' => 'Missing required field']);
    }

    // @TODO ideally id should be person id and not match id
    $match = $app['matches']->get($id, false, false);
    if (!$match) {
        return $app->json(null, 404, ['X-Error-Message' => "Match $id not found"]);
    }

    $host = $app['hosts']->get($id);

    if (!$host) {
        return $app->json(null, 404, ['X-Error-Message' => 'Person not found!']);
    }

    $hash = $app['email']->createHashCode($host['email']);

    if ($hash != $code) {
        error_log("Feedback request with invalid code [{$code}] != [{$hash}] for person [{$$host['id']}]");
        return $app->json(null, 400, ['X-Error-Message' => 'Invalid code!']);
    }

    $data = ['status' => People::STATUS_ACTIVE];
    $result = $app['people']->update($id, $data);
    if (!$result) {
        error_log("Failed to update match {$id}");
        return $app->json(null, 500, ['X-Error-Message' => 'Could not update person']);
    }

    return $app->json('OK');
});

$app->post('/feedback', function(Request $request) use ($app) {
    $r = $request->request;

    $id   = (int) $r->get('id');
    $code = $r->get('code');
    $status = (int) $r->get('status');

    if (empty($id) || empty($code) || empty($status)) {
        return $app->json(null, 400, ['X-Error-Message' => 'Missing required field']);
    }

    $match = $app['matches']->get($id, true, false);
    if (!$match) {
        return $app->json([], 404, ['X-Error-Message' => 'Match not found']);
    }

    $hash = $app['email']->createHashCode($match['host']['email']);
    if ($hash != $code) {
        error_log("Feedback request with invalid code [{$code}] != [{$hash}] for match [{$id}]");
        return $app->json(null, 400, ['X-Error-Message' => 'Invalid code!']);
    }

    $data = ['status' => $status];
    $result = $app['matches']->update($id, $data);
    if (!$result) {
        error_log("Failed to update match {$id}");
        return $app->json(null, 500, ['X-Error-Message' => 'Failed to update match']);
    }

    return $app->json('OK');
});