<?php

use Symfony\Component\HttpFoundation\Request;
use app\Environment;

$app->get('/guest/{id}', function ($id) use ($app) {

    $args = [(int) $id];
    $sql = "SELECT people.*, guests.food_concerns FROM people, guests WHERE people.id = guests.user_id AND people.id = ?";
    error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
    $guest = $app['db']->fetchAssoc($sql, $args);
    if (!$guest) {
        return $app->json(null, 404);
    }
    $guest = Environment::get('base_url');
    return $app->json($guest);
});

$app->get('/guests', function() use ($app) {
    $status = isset($_GET['status']) ? $_GET['status'] : 1;

    $args = [(int) $status];
    $sql = "SELECT people.*, guests.user_id FROM people, guests WHERE people.id = guests.user_id AND people.status = ? ORDER BY updated DESC";

    $children = isset($_GET['children']) ? $_GET['children'] : null;

    if ($children !== null && $children == 'yes') {
        $sql .= " AND people.children <> ?";
        $args[] = 0;
    }

    $men = isset($_GET['men']) ? $_GET['men'] : null;
    if ($men !== null && $men == 'yes') {
        $sql .= ' AND people.adults_m <> ?';
        $args[] = 0;
    }

    $women = isset($_GET['women']) ? $_GET['women'] : null;
    if ($women !== null && $women == 'yes') {
        $sql .= ' AND people.adults_f <> ?';
        $args[] = 0;
    }

    error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
    $guests = $app['db']->fetchAll($sql, $args);

    foreach ($guests as &$guest) {
        $now     = new \DateTime();
        $updated = new \DateTime($guest['created']);
        if ($updated->diff($now)->days == 0) {
            $guest['waited'] = "Added today";
        } else if ($updated->diff($now)->days == 1) {
            $guest['waited'] = $updated->diff($now)->days . " day";
        } else {
            $guest['waited'] = $updated->diff($now)->days . " days";
        }
    }

    return $app->json($guests);
});

