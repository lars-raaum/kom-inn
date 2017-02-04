<?php

use Symfony\Component\HttpFoundation\Request;
use app\Environment;

$app->get('/guest/{id}', function ($id) use ($app) {
    $guest = $app['guests']->get((int) $id);
    if (!$guest) {
        return $app->json(null, 404);
    }
    return $app->json($guest);
});

$app->get('/guests', function() use ($app) {
    $status = isset($_GET['status']) ? (int) $_GET['status'] : 1;
    $filters = [];
    $filters['children'] = isset($_GET['children']) ? $_GET['children'] : null;
    $filters['men'] = isset($_GET['men']) ? $_GET['men'] : null;
    $filters['women'] = isset($_GET['women']) ? $_GET['women'] : null;

    $guests = $app['guests']->find($status, $filters);
    return $app->json($guests);
});
