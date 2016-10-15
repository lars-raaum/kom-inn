<?php

use Symfony\Component\HttpFoundation\Request;

$app->post('/register', function(Request $request) use ($app, $types) {
    $r = $request->request;

    $type = $r->get('type');

    $curl     = new \Ivory\HttpAdapter\CurlHttpAdapter();
    $geocoder = new \Geocoder\Provider\GoogleMaps($curl);


    $defaults = [
        'status'    => 1,
        'updated'   => new DateTime('now'),
        'created'   => new DateTime('now')
    ];
    $data = [
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
        'freetext'  => $r->get('freetext'),
    ] + $defaults;

    $addresses = $geocoder->geocode($data['address'] . ' oslo ' . $data['zipcode'] . ' norway');
    $coords = $addresses->first()->getCoordinates();
    $data['loc_long'] = $coords->getLongitude();
    $data['loc_lat'] = $coords->getLatitude();

    // validation

    $result = $app['db']->insert('people', $data , $types);

    if (!$result) {
        return $app->json(['result' => false]);
    }
    $user_id = $app['db']->lastInsertId();

    $data = [
        'user_id' => $user_id,
        'updated' => new DateTime('now'),
        'created' => new DateTime('now')
    ];
    if ($type == 'host') {
        $result = $app['db']->insert('hosts', $data, $types);
        $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
    } else {
        $result = $app['db']->insert('guests', $data, $types);
        $sql = "SELECT people.*, guests.food_concerns FROM people, guests WHERE people.id = guests.user_id AND people.id = ?";
    }

    $person = $app['db']->fetchAssoc($sql, [(int) $user_id]);

    return $app->json($person);
});