<?php

use Symfony\Component\HttpFoundation\Request;
use app\Emailing;

$dtt = \Doctrine\DBAL\Types\Type::getType('datetime');
$types = ['updated' => $dtt, 'created' => $dtt];

$app->post('/reactivate', function(Request $request) use ($app, $types) {
    $r = $request->request;

    $id   = (int) $r->get('id');
    $code = $r->get('code');

    // @TODO ideally id should be person id and not match id

    $sql   = "SELECT * FROM `matches` WHERE id = ?";
    $args  = [$id];

    error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [public]");
    $match = $app['db']->fetchAssoc($sql, $args);

    if (!$match) {
        return $app->json([], 404, ['X-Error-Message' => 'Match not found']);
    }


    $sql = "SELECT * FROM `people` WHERE id = ?";
    $args = [$match['host_id']];
    error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [public]");
    $host = $app['db']->fetchAssoc($sql, $args);

    if (!$host) {
        return $app->json(null, 404, ['X-Error-Message' => 'Person not found!']);
    }

    $emailing = new Emailing();
    $hash = $emailing->createHashCode($host['email']);

    if ($hash != $code) {
        error_log("Feedback request with invalid code [{$code}] != [{$hash}] for person [{$$host['id']}]");
        return $app->json(null, 400, ['X-Error-Message' => 'Invalid code!']);
    }

    $data = ['status' => 0, 'updated' => new DateTime('now')];
    error_log("Update Person[{$host['id']}] by [public]");
    $result = $app['db']->update('people', $data, ['id' => (int) $host['id']], $types);

    if (!$result) {
        error_log("Failed to update match {$id}");
        return $app->json(null, 500);
    }

    return $app->json('OK');
});

$app->post('/feedback', function(Request $request) use ($app, $types) {
    $r = $request->request;

    $id   = (int) $r->get('id');
    $code = $r->get('code');
    $status = (int) $r->get('status');

    $sql   = "SELECT * FROM `matches` WHERE id = ?";
    $args  = [$id];

    error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [public]");
    $match = $app['db']->fetchAssoc($sql, $args);

    if (!$match) {
        return $app->json([], 404, ['X-Error-Message' => 'Match not found']);
    }

    $args = [(int) $match['host_id']];
    $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
    error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [public]");
    $match['host'] = $app['db']->fetchAssoc($sql, $args);

    $emailing = new Emailing();
    $hash = $emailing->createHashCode($match['host']['email']);

    if ($hash != $code) {
        error_log("Feedback request with invalid code [{$code}] != [{$hash}] for match [{$id}]");
        return $app->json(null, 400, ['X-Error-Message' => 'Invalid code!']);
    }

    $data = [
        'status' => $status,
        'updated' => new DateTime('now')
    ];
    error_log("Update Match[{$id}] by [public]");
    $result = $app['db']->update('matches', $data, ['id' => (int) $id], $types);

    if (!$result) {
        error_log("Failed to update match {$id}");
        return $app->json(null, 500);
    }

    return $app->json('OK');
});