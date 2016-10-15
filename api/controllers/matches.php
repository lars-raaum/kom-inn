<?php

use Symfony\Component\HttpFoundation\Request;

$app->post('/match', function(Request $request) use ($app, $types) {
    $r = $request->request;
    $data = [
        'guest_id' => $r->get('guest_id'),
        'host_id'  => $r->get('host_id'),
        'comment'  => $r->get('comment'),
        'updated'  => new DateTime('now'),
        'created'  => new DateTime('now')
    ];
    $result = $app['db']->insert('matches', $data, $types);
    if (!$result) {
        return $app->json(['result' => false]);
    }
    return $app->json(['result' => true]);
});

$app->get('/match/{id}', function ($id) use ($app) {

    $sql = "SELECT * FROM matches WHERE id = ?";
    $match = $app['db']->fetchAssoc($sql, [(int) $id]);
    if (!$match) {
        return $app->json(null, 404);
    }

    $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
    $match['host'] = $app['db']->fetchAssoc($sql, [(int) $match['host_id']]);

    $sql = "SELECT people.*, guests.food_concerns FROM people, guests WHERE people.id = guests.user_id AND people.id = ?";
    $match['guest'] = $app['db']->fetchAssoc($sql, [(int) $match['guest_id']]);

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
