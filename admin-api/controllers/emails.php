<?php

use Symfony\Component\HttpFoundation\Request;
use app\Emailing;

$app->post('/match/{id}/email/{type}', function($id, $type, Request $request) use ($app) {
    $sql = "SELECT * FROM matches WHERE id = ?";
    $match = $app['db']->fetchAssoc($sql, [(int) $id]);
    if (!$match) {
        return $app->json(null, 404);
    }

    $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
    $match['host'] = $app['db']->fetchAssoc($sql, [(int) $match['host_id']]);

    $sender = new Emailing();
    switch ($type) {
        case 'host_nag':
            $result = $sender->sendNaggingMail($match);
            break;
        case 'host_inform':
            $result = $sender->sendHostInform($match);
            break;
        default:
            error_log("Email type [$type] not supported");
            return $app->json(null, 500);
    }

    return $app->json(['sent' => $result]);
});
