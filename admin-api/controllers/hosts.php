<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('/host/{id}', function ($id) use ($app) {

    $host = $app['hosts']->get($id);
    if (!$host) {
        return $app->json(null, 404, ['X-Error-Message' => "Host $id not found!"]);
    }

    return $app->json($host);
});

$app->get('/hosts', function(Request $request) use ($app) {
    $guest_id = isset($_GET['guest_id']) ? (int) $_GET['guest_id'] : NULL;
    $distance = isset($_GET['distance']) ? (float) floatval($_GET['distance']) : 20; // distance in nautical miles squared

    if ($guest_id) {
        $filters = [
            'children'  => isset($_GET['children']) ? $_GET['children'] : null,
            'men'       => isset($_GET['men']) ? $_GET['men'] : null,
            'women'     => isset($_GET['women']) ? $_GET['women'] : null
        ];
        try {
            $hosts = $app['hosts']->findHostForGuest($guest_id, $distance, $filters);
        } catch (\Exception $e) {
            if ($e->getCode() == 404) {
                return $app->json([], 404, ['X-Error-Message' => 'Guest does not exist']);
            }
        }
    } else {
        $hosts = $app['hosts']->find();
    }

    return $app->json($hosts);
});
