<?php

$app->get('/guest/{id}', function ($id) use ($app) {
    return $app->json($app['guests']->get((int) $id));
});

$app->get('/guests', function() use ($app) {
    $status = (int) ($_GET['status'] ?? 1);
    $filters = [];
    $filters['children'] = $_GET['children'] ?? null;
    $filters['men'] = $_GET['men'] ?? null;
    $filters['women'] = $_GET['women'] ?? null;
    $filters['region'] = $_GET['region'] ?? null;

    $guests = $app['guests']->find($status, $filters);
    return $app->json($guests);
});
