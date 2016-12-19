<?php

use Symfony\Component\HttpFoundation\Request;

function get_person($id, $app) {

    $args = [(int) $id];
    $sql = "SELECT p.*, g.id as `guest_id`, g.food_concerns, h.id as `host_id` FROM people AS p ".
           "LEFT JOIN guests AS g ON (p.id = g.user_id) ".
           "LEFT JOIN hosts  AS h ON (p.id = h.user_id) ".
           "WHERE p.id = ?";
    error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
    $person = $app['db']->fetchAssoc($sql, $args);

    if (!$person) return null;
    if ($person['guest_id'] === NULL) {
        unset($person['guest_id']);
        unset($person['food_concerns']);
        $person['type'] = 'HOST';
    }
    if ($person['host_id'] === NULL) {
        unset($person['host_id']);
        $person['type'] = 'GUEST';
    }

    return $person;
}

$app->get('/person/{id}', function($id, Request $request) use ($app) {

    $person = get_person($id, $app);
    if (!$person) {
        return $app->json(null, 404);
    }

    return $app->json($person);
});


$app->post('/person/{id}', function($id, Request $request) use ($app, $types) {
    $id = (int) $id;

    $person = get_person($id, $app);
    if (!$person) {
        return $app->json(null, 404);
    }

    $updated = new DateTime('now');
    $r = $request->request;
    $types = ['updated' => \Doctrine\DBAL\Types\Type::getType('datetime')];
    $data  = [
        'email'     => $r->get('email'),
        'name'      => $r->get('name'),
        'phone'     => $r->get('phone'),
        'gender'    => $r->get('gender'),
        'age'       => $r->get('age'),
        'children'  => $r->get('children'),
        'adults_m'  => $r->get('adults_m'),
        'adults_f'  => $r->get('adults_f'),
        'bringing'  => $r->get('bringing'),
        'origin'    => $r->get('origin'),
        'zipcode'   => $r->get('zipcode'),
        'address'   => $r->get('address'),
        'status'    => $r->get('status'),
        'visits'    => $r->get('visits'),
        'freetext'  => $r->get('freetext'),
        'updated'   => $updated
    ];

    foreach ($data as $key => $value) {
        if ($value === null) {
            unset($data[$key]);
        }
    }

    error_log("Update person {$id} - by [{$_SERVER['PHP_AUTH_USER']}]");
    $result = $app['db']->update('people', $data, ['id' => $id], $types);
    if (!$result) {
        error_log("Failed to update person {$id}");
        return $app->json(null, 500);
    }

    $food_concerns = $r->get('food_concerns');
    if ($food_concerns) {
        error_log("Update guest {$person['guest_id']} - by [{$_SERVER['PHP_AUTH_USER']}]");
        $result = $app['db']->update('guests', compact('food_concerns', 'updated'), ['id' => $person['guest_id']], $types);

        if (!$result) {
            error_log("Failed to update guest {$person['guest_id']}");
        }
    }

    $person = get_person($id, $app);

    return $app->json($person);
});


$app->delete('/person/{id}', function ($id) use ($app) {
    $id = (int) $id;

    $person = get_person($id, $app);
    if (!$person) {
        return $app->json(null, 404);
    }

    $data  = [
        'name'      => '#DELETED#',
        'email'     => '#DELETED#',
        'phone'     => '#DELETED#',
        'address'   => '#DELETED#',
        'freetext'  => NULL,
        'bringing'  => NULL,
        'status'    => -1
    ];

    error_log("DELETING DATA for Person[{$id}] by [{$_SERVER['PHP_AUTH_USER']}]");
    $result = $app['db']->update('people', $data, ['id' => (int) $id]);
    return $app->json(true);
});

$app->get('/people', function() use ($app) {

    $status = false;
    if (isset($_GET['status'])) {
        $status = (int) $_GET['status'];
    }

    $offset = (int) 0;
    $limit  = (int) 10;

    if ($status !== false) {
        $args = [$status];
        $sql = "SELECT * FROM people WHERE status = ? ORDER BY updated DESC LIMIT {$offset}, $limit ";
    } else {
        $args = [];
        $sql = "SELECT * FROM people ORDER BY updated DESC LIMIT {$offset}, $limit ";
    }
    error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
    $people = $app['db']->fetchAll($sql, $args);

    return $app->json($people);
});

