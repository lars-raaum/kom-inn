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
	$result = $app['db']->insert('users', $data ,['updated' => $dtt, 'created' => $dtt]);

	$id = $app['db']->lastInsertId();
    $sql = "SELECT * FROM users WHERE id = ?";
    $guest = $app['db']->fetchAssoc($sql, [(int) $id]);

	return $app->json($guest);
});

$app->get('/api/guest/{id}', function ($id) use ($app) {

    $sql = "SELECT * FROM users WHERE id = ?";
    $guest = $app['db']->fetchAssoc($sql, [(int) $id]);

    return $app->json($guest);
});

$app->run();