<?php

$app->get('/importcsv/{name}', function($name) use ($app) {
    $counter = 0; $errors = [];
    $geo = new \app\Geo();
    $now = new \DateTime('now');
    $fn = realpath(__DIR__ . '/../../resources/') . '/' . $name . ".csv";
    if (($handle = fopen($fn, "r")) !== FALSE) {
        $raw = fgetcsv($handle, 0, ";");
        if ($raw === false) die('failed to get headers');
        $headers = array_map("utf8_encode", $raw);
        $headers = array_flip($headers);
        while (($raw = fgetcsv($handle, 0, ";")) !== FALSE) {
            $row = array_map("utf8_encode", $raw);
            $data  = [
                'status'    => $row[$headers['status']],
                'name'      => $row[$headers['name']],
                'gender'    => $row[$headers['gender']] ?: 'n/a',
                'age'       => (int) $row[$headers['age']],
                'children'  => $row[$headers['children']] ?: 0,
                'adults_m'  => $row[$headers['adults_m']] ?: 0,
                'adults_f'  => $row[$headers['adults_f']] ?: 0,
                'address'   => $row[$headers['address']],
                'zipcode'   => substr($row[$headers['zipcode']], 0, 10),
                'origin'    => substr($row[$headers['origin']], 0, 128),
                'phone'     => substr($row[$headers['phone']], 0, 16),
                'email'     => $row[$headers['email']],
                'freetext'  => $row[$headers['freetext']],
                'bringing'  => $row[$headers['bringing']],
                'visits'    => $row[$headers['visits']] ?: 0,
                'created'   => $now,
                'updated'   => $now
            ];

            if ($data['email'] == 'email') {
                continue;
            }

            if (!empty($row[$headers['created']])) {
                $data['created'] = new \DateTime($row[$headers['created']]);
            }

            try {
                $coords = $geo->getCoords($data);
                $data['loc_long'] = $coords->getLongitude();
                $data['loc_lat'] = $coords->getLatitude();
            } catch (\Exception $e) {

            }
            // print_r($data); continue;
            if ($data['status'] == "") {
                print_r($data);
                print_r($row);
                die('FAILED');
            }

            $dtt = \Doctrine\DBAL\Types\Type::getType('datetime');
            $types = ['updated' => $dtt, 'created' => $dtt];

            try {
                $result = $app['db']->insert('people', $data, $types);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                continue;
            }
            if (!$result) {
              return $app->json(['result' => false]);
            }

            $user_id = $app['db']->lastInsertId();
            $related_data = [
                'user_id' => $user_id,
                'updated' => $data['created'],
                'created' => $now
            ];

            if ($row[$headers['type']] == 'host') {
                $result = $app['db']->insert('hosts', $related_data, $types);
            } else {
                $related_data['food_concerns'] = $row[$headers['food_concerns']];
                $result = $app['db']->insert('guests', $related_data, $types);
            }

            $counter++;
        }
        fclose($handle);
    }
    $out = ['result' => true, 'imported' => $counter];
    if ($errors) {
        $out['result'] = false;
        $out['fails'] = count($errors);
        $out['errors'] = $errors;
    }
    return $app->json($out);
});