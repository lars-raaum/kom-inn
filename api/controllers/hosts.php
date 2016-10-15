<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('/host/{id}', function ($id) use ($app) {

    $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
    $host = $app['db']->fetchAssoc($sql, [(int) $id]);
    if (!$host) {
        return $app->json(null, 404);
    }

    return $app->json($host);
});

$app->get('/hosts', function(Request $request) use ($app) {
    $status = isset($_GET['status']) ? $_GET['status'] : 1;
    $args = [(int) $status];
    $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.status = ?";

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

    error_log("SQL [ $sql ] [" . join(', ', $args) . "]");
    $hosts = $app['db']->fetchAll($sql, $args);
    return $app->json($hosts);
});
