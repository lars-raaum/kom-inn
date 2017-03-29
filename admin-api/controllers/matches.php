<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use app\models\Matches;
use app\exceptions\ServiceException;
use app\exceptions\ApiException;

/**
 * Get match by id
 *
 * @param int $id
 * @return Response
 * @throws \app\exceptions\ApiException
 */
$app->get('/match/{id}', function ($id) use ($app) {
    return $app->json($app['matches']->get((int) $id));
});

/**
 * Get all matches
 *
 * @return Response
 */
$app->get('/matches', function() use ($app) {
    $status = (int) ($_GET['status'] ?? 0);
    $limit = (int) ($_GET['limit'] ?? 10);
    $page = (int) ($_GET['page'] ?? 1);
    $filters = [
        'region' => $_GET['region'] ?? false,
        'host' => true,
        'guest' => true
    ];
    $matches = $app['matches']->find($status, $filters, compact('limit', 'page'));
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
        throw new ApiException('Missing required fields guest_id and host_id', 400);
    }

    $data = [
        'guest_id' => $guest_id,
        'host_id'  => $host_id,
        'comment'  => $r->get('comment'),
    ];
    $id = $app['matches']->insert($data);
    if ($id === false) {
        throw new ServiceException('Failed to insert match');
    }

    $result = $app['people']->setToUsed($guest_id);
    if (!$result) {
        error_log("ERROR: Failed to updated person {$guest_id} to be used!");
        throw new ServiceException('Failed to update guest');
    }

    $result = $app['people']->setToUsed($host_id);
    if (!$result) {
        error_log("Failed to updated person {$host_id} to be used!");
        throw new ServiceException('Failed to update host');
    }

    $match = $app['matches']->get($id);
    if (!$match) {
        throw new ServiceException('Inserted match not found!');
    }

    $app['sms']->sendHostInform($match);
    $app['mailer']->sendHostInform($match);

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
    $match = $app['matches']->get((int) $id, false, false);

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
        throw new ApiException('Nothing to save', 400);
    }

    $saved = $app['matches']->update($id, $data);
    if (!$saved) {
        throw new ServiceException("Unable to update Match $id");
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

    if ($match['status'] == Matches::STATUS_DELETED) {
        throw new ApiException("Match $id is already deleted", 400);
    }

    $result = $app['matches']->delete($match['id']);
    if (!$result) {
        throw new ServiceException("Could not delete match $id");
    }

    $result = $app['people']->setToActive($match['guest_id']);
    if (!$result) {
        throw new ServiceException("Failed to updated person {$match['guest_id']} to be used!");
    }
    $result = $app['people']->setToActive($match['host_id']);
    if (!$result) {
        throw new ServiceException("Failed to updated person {$match['host_id']} to be used!");
    }

    return $app->json(['result' => true]);
});
