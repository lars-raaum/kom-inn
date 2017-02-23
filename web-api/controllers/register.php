<?php

use app\exceptions\ApiException;
use app\exceptions\ServiceException;
use Symfony\Component\HttpFoundation\Request;

$app->post('/register', function(Request $request) use ($app) {
    $r = $request->request;

    $type = $r->get('type');

    $data = [
        'email'     => $r->get('email') ?: 'N/A',
        'name'      => $r->get('name') ?: 'N/A',
        'phone'     => $r->get('phone') ?: 'N/A',
        'gender'    => $r->get('gender') ?: 'n/a',
        'age'       => $r->get('age') ?: 0,
        'children'  => $r->get('children') ?: 0,
        'adults_m'  => $r->get('adults_m') ?: 0,
        'adults_f'  => $r->get('adults_f') ?: 0,
        'bringing'  => $r->get('bringing') ?: null,
        'origin'    => $r->get('origin') ?: '',
        'zipcode'   => $r->get('zipcode') ?: '',
        'address'   => $r->get('address') ?: '',
        'freetext'  => $r->get('freetext') ?: null,
    ];

    $data['adults_m'] += $data['gender'] == 'male' ? 1 : 0;
    $data['adults_f'] += $data['gender'] == 'female' ? 1 : 0;

    // validation
    if (!$type || !in_array($type, ['host', 'guest'])) {
        throw new ApiException('No `type` provided');
    }
    if ($data['email'] == 'N/A' && $data['phone'] == 'N/A') {
        throw new ApiException('Required data missing');
    }

    $id = $app['people']->insert($data);

    if (!$id) {
        throw new ServiceException('Unable to create person');
    }

    $data = ['user_id' => $id];

    if ($type == 'host') {
        $result = $app['hosts']->insert($data);
    } else {
        $data['food_concerns'] = $r->get('food_concerns');
        $result = $app['guests']->insert($data);
        $app['email']->sendAdminRegistrationNotice();
    }
    if (!$result) {
        throw new ServiceException("Unable to create $type person {$id}");
    }

    $person = $app['people']->get($id);
    return $app->json($person);
});
