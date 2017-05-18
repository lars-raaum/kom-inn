<?php

$app->get('/host/{id}', function ($id) use ($app) {
    return $app->json($app['hosts']->get((int) $id));
});

$app->get('/hosts', function() use ($app) {
    $guest_id = (int) ($_GET['guest_id'] ?? NULL);
    $distance = (float) (floatval($_GET['distance'] ?? 20 )); // distance in nautical miles squared

    if ($guest_id) {
        $filters = [
            'children'  => $_GET['children']  ?? null,
            'men'       => $_GET['men']  ?? null,
            'women'     => $_GET['women']  ?? null
        ];
        $childless = $_GET['childless'] ?? false;
        if ($childless == 'yes') {
            $filters['children'] = 'no';
        }
        $hosts = $app['hosts']->findHostForGuest($guest_id, $distance, $filters);
    } else {
        $hosts = $app['hosts']->find();
    }

    return $app->json($hosts);
});
