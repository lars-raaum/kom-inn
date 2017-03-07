<?php

use Symfony\Component\HttpFoundation\Request;
use app\models\People;
use app\exceptions\ServiceException;
use app\exceptions\ApiException;


$app->get('/person/{id}', function($id, Request $request) use ($app) {
    return $app->json($app['people']->get((int) $id));
});


$app->post('/person/{id}', function($id, Request $request) use ($app) {
    $person = $app['people']->get((int) $id);
    $r = $request->request;
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
    ];

    $saved = $app['people']->update($id, $data);

    if (!$saved) {
        throw new ServiceException('Unable to save person data');
    }

    if ($person['type'] === People::TYPE_GUEST) {
        $food_concerns = $r->get('food_concerns');
        if ($food_concerns) {
            $guest_id = $person['guest_id'];
            $saved = $app['guests']->update($guest_id, compact('food_concerns'));
            if (!$saved) {
                throw new ServiceException('Unable to save guest data');
            }
        }
    }

    $person = $app['people']->get($id);

    return $app->json($person);
});


$app->delete('/person/{id}', function ($id) use ($app) {
    $person = $app['people']->get((int) $id);
    if ($person['status'] == People::STATUS_DELETED) {
        throw new ApiException("Person $id is already deleted");
    }
    $result = $app['people']->delete($id);
    return $app->json($result);
});

$app->get('/people', function() use ($app) {
    $offset = 0;
    $status = false;
    if (isset($_GET['status'])) {
        $status = (int) $_GET['status'];
    }
    $limit = (int) ($_GET['limit'] ?? 10);
    if (isset($_GET['page'])) {
        $page = (int) $_GET['page'];
        $offset = $page * $limit - $limit;
    } else {
        $page = 1;
    }

    $people = $app['people']->find($status, $limit, $offset);
    $total = $app['people']->total($status);

    $count = count($people);

    // ($data = null, $status = 200, $headers = array(), $json = false)
    return $app->json($people, 200, ['X-Limit' => $limit, 'X-Offset' => $offset, 'X-Total' => $total, 'X-Page' => $page, 'X-Count' => $count]);
});

