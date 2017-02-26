<?php

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
        return $app->json(null, 400, ['X-Error-Message' => 'No `type` provided']);
    }
    if ($data['email'] == 'N/A' && $data['phone'] == 'N/A') {
        return $app->json(null, 400, ['X-Error-Message' => 'Required data missing']);
    }

    $id = $app['people']->insert($data);

    if (!$id) {
        return $app->json(null, 500, ['X-Error-Message' => 'Unable to create person']);
    }

    $data = ['user_id' => $id];

    if ($type == 'host') {
        $result = $app['hosts']->insert($data);
    } else {
        $data['food_concerns'] = $r->get('food_concerns');
        $result = $app['guests']->insert($data);
        $app['mailer']->sendAdminRegistrationNotice();
    }
    if (!$result) {
        error_log("Failed to create $type record for person {$id}");
        return $app->json(null, 500, ['X-Error-Message' => "Unable to create $type person"]);
    }

    $person = $app['people']->get($id);
    return $app->json($person);
});
