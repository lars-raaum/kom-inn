<?php

$app->get('/api/guest/{id}', function ($id) use ($app) {

    $sql = "SELECT people.*, guests.food_concerns FROM people, guests WHERE people.id = guests.user_id AND people.id = ?";
    $guest = $app['db']->fetchAssoc($sql, [(int) $id]);

    return $app->json($guest);
});

$app->get('/api/guests', function() use ($app) {
    $status = $_GET['status'] ?: 1;

    $args = [(int) $status];
    $sql = "SELECT people.*, guests.user_id FROM people, guests WHERE people.id = guests.user_id AND people.status = ?";

    $children = $_GET['children'] ?: null;
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

    error_log("SQL [ $sql ] [" . join(', ', $args) . "]");
    $guests = $app['db']->fetchAll($sql, $args);
    return $app->json($guests);
});

