<?php

use Symfony\Component\HttpFoundation\Request;
use app\models\People;
use app\exceptions\ServiceException;
use app\exceptions\ApiException;


$app->get('/person/{id}', function($id, Request $request) use ($app) {
    return $app->json($app['people']->get((int) $id));
});

$app->post('/person/{id}/sorry', function($id, Request $request) use ($app) {
    $person = $app['people']->get((int) $id);
    if (!$person) {
        return $app->json(null, 404);
    }

    if ($person['status'] != People::STATUS_ACTIVE) {

        return $app->json(null, 400, ['X-Error-Message' => 'Only Active users may be deleted!']);
    }

    /** @var People $people */
    $people = $app['people'];
    $people->setToSoftDeleted($id);
    /** @var \app\Mailer $mailer */
    $mailer = $app['mailer'];
    $sent = $mailer->sendSorryMail($person);
    if ($sent == false) {
        throw new ApiException('Failed to send email', 500);
    }
    return $app->json(["sent" => true]);
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
        'freetext'  => $r->get('freetext'),
        'admin_comment'  => $r->get('admin_comment'),
    ];

    foreach ($data as $key => $value) {
        if ($person[$key] === $value) {
            unset($data[$key]);
        }
    }
    if (isset($data['address']) && !isset($data['zipcode'])) {
        $data['zipcode'] = $person['zipcode'];
    }
    if (!isset($data['address']) && isset($data['zipcode'])) {
        $data['address'] = $person['address'];
    }

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
    $result = $app['people']->setToSoftDeleted($id);
    return $app->json($result);
});

$app->post('/person/{id}/convert', function ($id) use ($app) {
    $id = (int) $id;

    $result = $app['people']->changeTypeOfPerson($id);
    if (!$result) {
        return $app->json(null, 404);
    }
    $result = $app['people']->get($id);
    return $app->json($result);
});

$app->get('/people', function() use ($app) {
    $offset = 0;
    $status = false;
    if (isset($_GET['status'])) {
        $status = (int) $_GET['status'];
    } else {
        $status = false;
    }

    if (isset($_GET['region'])) {
        $reg = strtoupper(trim($_GET['region']));
        switch ($reg) {
            case People::REGION_NORWAY:
            case People::REGION_UNKNOWN:
            case People::REGION_OSLO:
                $region = $reg;
                break;
            default:
                return $app->json(null, 400, ['X-Error-Message' => 'Bad region, support OSLO, NORWAY, UNKNOWN']);
        }
    } else {
        $region = null;
    }

    $limit = (int) ($_GET['limit'] ?? 10);
    if (isset($_GET['page'])) {
        $page = (int) $_GET['page'];
        $offset = $page * $limit - $limit;
    } else {
        $page = 1;
    }
    /** @var People $app['people'] */
    $people = $app['people']->find($status, $limit, $offset, $region);
    $total = $app['people']->total($status, $region);

    $count = count($people);

    // ($data = null, $status = 200, $headers = array(), $json = false)
    return $app->json($people, 200, ['X-Limit' => $limit, 'X-Offset' => $offset, 'X-Total' => $total, 'X-Page' => $page, 'X-Count' => $count]);
});

