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
            return $app->json(null, 500, ['X-Error-Message' => "Email type [$type] not supported"]);
    }

    if ($result) {
        return $app->json(['sent' => true]);
    } else {
        return $app->json(['sent' => false], 500, ['X-Error-Message' => 'Not sent, is it configured?']);
    }
});
