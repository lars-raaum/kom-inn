<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});
require_once __DIR__.'/../../resources/configuration.php';


$dtt = \Doctrine\DBAL\Types\Type::getType('datetime');
$types = ['updated' => $dtt, 'created' => $dtt];

$app->post('/api/register', function(Request $request) use ($app, $types) {
	$r = $request->request;

	$type = $r->get('type');

	$defaults = [
		'status' 	=> 1,
		'updated' 	=> new DateTime('now'),
		'created' 	=> new DateTime('now')
	];
	$data = [
		'email' 	=> $r->get('email'),
		'name' 		=> $r->get('name'),
		'phone' 	=> $r->get('phone'),
		'gender' 	=> $r->get('gender'),
		'age' 		=> $r->get('age'),
		'children' 	=> $r->get('children'),
		'adults_m' 	=> $r->get('adults_m'),
		'adults_f' 	=> $r->get('adults_f'),
		'origin' 	=> $r->get('origin'),
		'zipcode' 	=> $r->get('zipcode'),
		'address' 	=> $r->get('address'),
		'freetext' 	=> $r->get('freetext'),
	] + $defaults;

	// validation

	$result = $app['db']->insert('people', $data , $types);

	if (!$result) {
		return $app->json(['result' => false]);
	}
	$user_id = $app['db']->lastInsertId();

	$data = [
		'user_id' => $user_id,
		'updated' => new DateTime('now'),
		'created' => new DateTime('now')
	];
	if ($type == 'host') {
		$result = $app['db']->insert('hosts', $data, $types);
	    $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
	} else {
		$result = $app['db']->insert('guests', $data, $types);
	    $sql = "SELECT people.*, guests.food_concerns FROM people, guests WHERE people.id = guests.user_id AND people.id = ?";
	}

    $person = $app['db']->fetchAssoc($sql, [(int) $user_id]);

	return $app->json($person);
});

$app->post('/api/match', function(Request $request) use ($app, $types) {
	$r = $request->request;
	$data = [
		'guest_id' => $r->get('guest_id'),
		'host_id'  => $r->get('host_id'),
		'comment'  => $r->get('comment'),
		'updated'  => new DateTime('now'),
		'created'  => new DateTime('now')
	];
	$result = $app['db']->insert('matches', $data, $types);
	if (!$result) {
		return $app->json(['result' => false]);
	}
	return $app->json(['result' => true]);
});

$app->get('/api/matches', function(Request $request) use ($app) {
	$status = 0; // matched
	$sql = "SELECT * FROM matches WHERE status = ?";
	$matches = $app['db']->fetchAll($sql, [(int) $status]);
	foreach ($matches as $k => $match) {

	    $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
		$matches[$k]['host'] = $app['db']->fetchAssoc($sql, [(int) $match['host_id']]);

	    $sql = "SELECT people.*, guests.food_concerns FROM people, guests WHERE people.id = guests.user_id AND people.id = ?";
	    $matches[$k]['guest'] = $app['db']->fetchAssoc($sql, [(int) $match['guest_id']]);
	}
	return $app->json($matches);
});

$app->get('/api/guest/{id}', function ($id) use ($app) {

    $sql = "SELECT people.*, guests.food_concerns FROM people, guests WHERE people.id = guests.user_id AND people.id = ?";
    $guest = $app['db']->fetchAssoc($sql, [(int) $id]);

    return $app->json($guest);
});


$app->get('/api/host/{id}', function ($id) use ($app) {

    $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
    $host = $app['db']->fetchAssoc($sql, [(int) $id]);

    return $app->json($host);
});

$app->get('/api/hosts', function(Request $request) use ($app) {
	$status = $_GET['status'] ?: 1;
	$args = [(int) $status];
	$sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.status = ?";

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
    $hosts = $app['db']->fetchAll($sql, $args);
    return $app->json($hosts);
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


$app->run();