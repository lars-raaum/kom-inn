<?php

use Symfony\Component\HttpFoundation\Request;

$app->post('/match/{id}/email/{type}', function($id, $type, Request $request) use ($app) {

    $match = $app['matches']->get((int) $id, true, false); // Only include host
    if (!$match) {
        return $app->json(null, 404, ['X-Error-Message' => 'Match $id not found']);
    }

    switch ($type) {
        case 'host_nag':
            $result = $app['email']->sendNaggingMail($match);
            break;
        default:
            error_log("Email type [$type] not supported");
            return $app->json(null, 500);
    }

    return $app->json(['sent' => $result]);
});
