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

$app->post('/api/register', function(Request $request) use ($app) {
	$r = $request->request;

	$type = $r->get('type');

	$defaults = [
		'status' => 1,
		'updated' => new DateTime('now'),
		'created' => new DateTime('now')
	];
	$data = [
		'email' => $r->get('email'),
		'name' => $r->get('name'),
		'gender' => $r->get('gender'),
		'age' => $r->get('age'),
		'children' => $r->get('children'),
		'adults_m' => $r->get('adults_m'),
		'adults_f' => $r->get('adults_f'),
		'origin' => $r->get('origin'),
		'zipcode' => $r->get('zipcode'),
		'address' => $r->get('address'),
		'freetext' => $r->get('freetext'),
	] + $defaults;

	// validation

	$dtt = \Doctrine\DBAL\Types\Type::getType('datetime');
	$types = ['updated' => $dtt, 'created' => $dtt];
	$result = $app['db']->insert('people', $data , $types);

	if (!$result) {
		return $app->json(false);
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

$app->get('/api/hosts', function() use ($app) {
	$sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id";
    $hosts = $app['db']->fetchAll($sql);
    return $app->json($hosts);
});

$app->get('/api/guests', function() use ($app) {
	$sql = "SELECT people.*, guests.user_id FROM people, guests WHERE people.id = guests.user_id";
    $guests = $app['db']->fetchAll($sql);
    return $app->json($guests);
});

$app->run();