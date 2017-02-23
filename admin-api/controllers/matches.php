<?php

use Symfony\Component\HttpFoundation\Request;
use app\models\Matches;
use Symfony\Component\HttpFoundation\Response;

/**
 * Get match by id
 *
 * @param int $id
 * @return Response
 * @error 404 Match not found
 */
$app->get('/match/{id}', function ($id) use ($app) {
    $match = $app['matches']->get((int) $id);
    if (!$match) return $app->json(null, 404, ['X-Error-Message' => "Match $id not found!"]);
    return $app->json($match);
});

/**
 * Get all matches
 *
 * @return Response
 */
$app->get('/matches', function() use ($app) {
    $status = (int) ($_GET['status'] ?? 0);

    // @TODO add pagination
    $matches = $app['matches']->find($status);
    return $app->json($matches);
});

/**
 * Create new match
 *
 * @param Request $request
 * @return Response
 * @error 400 Missing required fields
 * @error 500 Failed to insert match
 * @error 500 Failed to update guest
 * @error 500 Failed to update host
 * @error 500 Could not get inserted match
 */
$app->post('/match', function(Request $request) use ($app) {
    $r = $request->request;
    $guest_id = $r->get('guest_id');
    $host_id  = $r->get('host_id');

    #validation
    if (empty($guest_id) || empty($host_id)) {
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

    $app['sms']->sendHostInform($match);
    $app['email']->sendHostInform($match);

    return $app->json($match);
});

/**
 * Update match
 *
 * @param int $id
 * @param Request $request
 * @return Response
 * @error 404 Match not found
 * @error 400 No save needed or valid
 * @error 500 Failed to save
 */
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

/**
 * Soft deletes a match and sets both host and guest back to active
 *
 * @path /match/{id}
 * @arg int $id Match id
 * @error 404 Match not found
 * @error 400 Match already deleted
 * @error 500 Could not delete (db error)
 * @error 500 Failed to update host or guest (db error)
 * $response 200 {"result": true}
 * @param int $id
 * @param Request $request
 * @return Response
 */
$app->delete('/match/{id}', function ($id) use ($app) {
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
