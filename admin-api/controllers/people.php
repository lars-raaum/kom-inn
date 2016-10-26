<?php

use Symfony\Component\HttpFoundation\Request;

$app->post('/person/{id}', function($id, Request $request) use ($app, $types) {

    $sql = "SELECT people.* FROM people WHERE people.id = ?";
    $person = $app['db']->fetchAssoc($sql, [(int) $id]);

    if (!$person) {
        return $app->json(null, 404);
    }

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
        'origin'    => $r->get('origin'),
        'zipcode'   => $r->get('zipcode'),
        'address'   => $r->get('address'),
        'status'    => $r->get('status'),
        'visits'    => $r->get('visits'),
        'freetext'  => $r->get('freetext'),
        'updated'   => new DateTime('now')
    ];

    foreach ($data as $key => $value) {
        if ($value === null) {
            unset($data[$key]);
        }
    }

    $result = $app['db']->update('people', $data, ['id' => (int) $id], $types);
    if (!$result) {
        error_log("Failed to update match {$id}");
        return $app->json(null, 500);
    }

    $sql = "SELECT people.* FROM people WHERE people.id = ?";
    $person = $app['db']->fetchAssoc($sql, [(int) $id]);

    return $app->json($person);
});


$app->delete('/person/{id}', function ($id) use ($app) {
    $app['db']->delete('hosts', array('user_id' => $id));
    $app['db']->delete('guests', array('user_id' => $id));
    $app['db']->delete('people', array('id' => $id));

    return $app->json(true);
});
