<?php

use Symfony\Component\HttpFoundation\Request;
use app\Environment;

$app->get('/guest/{id}', function ($id) use ($app) {
    $guest = $app['guests']->get((int) $id);
    if (!$guest) {
        return $app->json(null, 404, ['X-Error-Message' => "Guest $id not found"]);
    }
    return $app->json($guest);
});

$app->get('/guests', function() use ($app) {
    $status = (int) ($_GET['status'] ?? 1);
    $filters = [];
    $filters['children'] = $_GET['children'] ?? null;
    $filters['men'] = $_GET['men'] ?? null;
    $filters['women'] = $_GET['women'] ?? null;

    $guests = $app['guests']->find($status, $filters);
    return $app->json($guests);
});
