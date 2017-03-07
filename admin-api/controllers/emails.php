<?php

use Symfony\Component\HttpFoundation\Response;

/**
 * Triggers email sending for match
 *
 * @path /match/{id}/email/{type}
 * @param int $id
 * @param string $type
 * @return Response
 * @throws \app\exceptions\ApiException
 * @throws \app\exceptions\ServiceException
 */
$app->post('/match/{id}/email/{type}', function($id, $type) use ($app) {
    $match = $app['matches']->get((int) $id, true, false); // Only include host

    switch ($type) {
        case 'reminder':
            $result = $app['mailer']->sendReminderMail($match);
            break;
        default:
            throw new \app\exceptions\ApiException("Email type [$type] not supported");
    }

    if (!$result) {
        throw new app\exceptions\ServiceException('Email is not sent, is it configured?');
    }

    return $app->json(['sent' => true]);
});
