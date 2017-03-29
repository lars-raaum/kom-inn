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

/**
 * Show email content body
 *
 * @path /match/{id}/email/{type}
 * @param int $id
 * @param string $type
 * @return Response
 * @throws \app\exceptions\ApiException
 * @throws \app\exceptions\ServiceException
 */
$app->post('/match/{id}/email/{template}/render', function($id, $template) use ($app) {
    if (\app\Environment::env() !== 'dev') {
        throw new \app\exceptions\ApiException("Endpoint only exists in DEV", 404);
    }

    $match = $app['matches']->get((int) $id, true, true);

    /** @var \app\Mailer $mailer */
    $mailer = $app['mailer'];

    switch ($template) {
        case 'reminder':
            $templater = new \app\mails\Reminders($mailer);
            $type = $_GET['type'] ?? 'default';
            switch ($type) {
                case 'FIRST':
                    $content = $templater->buildFeedbackRequestText2days($match);
                    break;
                case 'SECOND':
                    $content = $templater->buildFeedbackRequestText4days($match);
                    break;
                case 'THIRD':
                    $content = $templater->buildFeedbackRequestText7days($match);
                    break;
                default:
                    $content = $templater->buildFeedbackRequestText($match);
            }
            break;
        case 'host_inform':
            $templater = new \app\mails\HostInform($mailer);
            $content = $templater->buildHostInformText($match);
            break;
        default:
            throw new \app\exceptions\ApiException("Email type [$type] not supported");
    }

    if (!$content) {
        throw new app\exceptions\ServiceException('Email is not sent, is it configured?');
    }

    return $content;
});
