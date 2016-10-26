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
    $distance = isset($_GET['distance']) ? pow(floatval($_GET['distance']) * 0.539956803 / 60, 2) : NULL; // distance in nautical miles squared

//
//    if ($longitude != NULL and $latitude != NULL and $distance != NULL) {
//
//    }

    $args = [(int) $status];
    $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.status = ?";

    $latitude = NULL;
    $longitude = NULL;

    if ($guest_id != NULL and $distance != NULL) {
        $q = "select people.loc_lat, people.loc_long from people where people.id = ?";
        $people = $app['db']->fetchAll($q, [$guest_id]);

        if (isset($people[0])) {
            $latitude = floatval($people[0]['loc_lat']);
            $longitude = floatval($people[0]['loc_long']);
            if ($latitude && $longitude) {
                $sql .=  " AND (people.loc_long - $longitude)*(people.loc_long - $longitude) + (people.loc_lat - $latitude)*(people.loc_lat - $latitude) < $distance ";
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

    error_log("SQL [ $sql ] [" . join(', ', $args) . "]");
    $hosts = $app['db']->fetchAll($sql, $args);

    for ($i = 0; $i < count($hosts); $i++) {
        $h_lat = $hosts[$i]['loc_lat'];
        $h_loc = $hosts[$i]['loc_long'];

        $dist = sqrt(pow($h_loc - $longitude, 2) + pow($h_lat - $latitude, 2)) * 60 / 0.539956803;
        $hosts[$i]['distance'] = $dist;
    }

    usort($hosts, function($a, $b) {
        return $a['distance'] > $b['distance'];
    });

    return $app->json($hosts);
});
