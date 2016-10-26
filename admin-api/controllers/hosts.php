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

    $guest_id = isset($_GET['guest_id']) ? $_GET['guest_id'] : NULL;
    $distance = isset($_GET['distance']) ? pow(floatval($_GET['distance']) * 0.539956803 / 60, 2) : 20; // distance in nautical miles squared

    $args = [(int) $status];
    $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.status = ?";

    $target_latitude = NULL;
    $target_longitude = NULL;

    if ($guest_id != NULL && $distance != NULL) {
        $sub_args = [$guest_id];
        $sub_sql = "SELECT people.loc_lat, people.loc_long FROM people WHERE people.id = ?";
        error_log("SQL [ $sub_sql ] [" . join(', ', $sub_args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
        $people = $app['db']->fetchAll($sub_sql, $sub_args);

        if (isset($people[0])) {
            $target_latitude = floatval($people[0]['loc_lat']);
            $target_longitude = floatval($people[0]['loc_long']);
            if ($target_latitude && $target_longitude) {
                $sql .=  " AND (people.loc_long - ?)*(people.loc_long - ?) + (people.loc_lat - ?)*(people.loc_lat - ?) < ? ";
                $args[] = $target_longitude;
                $args[] = $target_longitude;
                $args[] = $target_latitude;
                $args[] = $target_latitude;
                $args[] = $distance;
            }
        }
    }

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
    $hosts = $app['db']->fetchAll($sql, $args);

    if ($target_longitude && $target_latitude) {
        for ($i = 0; $i < count($hosts); $i++) {
            $h_lat = $hosts[$i]['loc_lat'];
            $h_loc = $hosts[$i]['loc_long'];
            $dist = sqrt(pow($h_loc - $target_longitude, 2) + pow($h_lat - $target_latitude, 2)) * 60 / 0.539956803;
            $hosts[$i]['distance'] = $dist;
        }

        usort($hosts, function($a, $b) {
            return $a['distance'] > $b['distance'];
        });
    }

    return $app->json($hosts);
});
