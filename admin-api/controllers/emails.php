<?php

use app\mails\HostInform;
use app\mails\Purge;
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
 * @path /email/{template}/render?id={id}
 * @param string $template
 * @return Response
 * @throws \app\exceptions\ApiException
 * @throws \app\exceptions\ServiceException
 */
$app->post('/email/{template}/render', function($template) use ($app) {
    if (\app\Environment::env() !== 'dev') {
        throw new \app\exceptions\ApiException("Endpoint only exists in DEV", 404);
    }

    $id = $_GET['id'] ?? 1;

    /** @var \app\Mailer $mailer */
    $mailer = $app['mailer'];

    switch ($template) {
        case 'reminder':
            $match = $app['matches']->get((int) $id, true, true);
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
        case 'expired_host':
            $templater = new Purge($mailer);
            $host = $app['people']->get((int) $id);
            $content = $templater->buildExpiredHostText($host);
            break;
        case 'reactivate_used':
            $templater = new Purge($mailer);
            $match = $app['people']->get((int) $id);
            $content = $templater->buildReactivateUsedText($match);
            break;
        case 'host_inform':
            $templater = new HostInform($mailer);
            $match = $app['matches']->get((int) $id, true, true);
            $content = $templater->buildHostInformText($match);
            break;
        default:
            throw new \app\exceptions\ApiException("Email type [$template] not supported");
    }

    if (!$content) {
        throw new app\exceptions\ServiceException('Email is not sent, is it configured?');
    }

    return $content;
});
