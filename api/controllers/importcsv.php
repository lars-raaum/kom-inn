<?php

$app->get('/importcsv/{name}', function($name) use ($app, $types) {
    $counter = 0;
    $geo = new \app\Geo();
    $now = new \DateTime('now');
    $fn = realpath(__DIR__ . '/../../resources/') . '/' . $name . ".csv";
    if (($handle = fopen($fn, "r")) !== FALSE) {
        while (($raw = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $row = array_map("utf8_encode", $raw);
            // print_r($row); continue;
            $data  = [
                'status'    => $row[0],
                'name'      => $row[2],
                'gender'    => $row[3] == 'm' ? 'male' : 'female',
                'age'       => $row[4],
                'children'  => $row[5] ?: 0,
                'adults_m'  => $row[6] ?: 0,
                'adults_f'  => $row[7] ?: 0,
                'address'   => $row[8],
                'zipcode'   => $row[9],
                'origin'    => $row[10],
                'phone'     => $row[11],
                'email'     => $row[12],
                'freetext'  => $row[13],
                'visits'    => $row[14] ?: 0,
                'created'   => $now,
                'updated'   => $now
            ];

            if ($data['email'] == 'email') {
                continue;
            }

            if (!empty($row[16])) {
                $data['created'] = new \DateTime($row[16]);
            }

            try {
                $coords = $geo->getCoords($data);
                $data['loc_long'] = $coords->getLongitude();
                $data['loc_lat'] = $coords->getLatitude();
            } catch (\Exception $e) {

            }
            // print_r($data); continue;

            $result = $app['db']->insert('people', $data, $types);
            if (!$result) {
              return $app->json(['result' => false]);
            }

            $user_id = $app['db']->lastInsertId();
            $related_data = [
                'user_id' => $user_id,
                'updated' => $data['created'],
                'created' => $now
            ];

            if ($row[1] == 'host') {
                $result = $app['db']->insert('hosts', $related_data, $types);
            } else {
                $related_data['food_concerns'] = $row[15];
                $result = $app['db']->insert('guests', $related_data, $types);
            }

            $counter++;
        }
        fclose($handle);
    }

    return $app->json(['result' => true, 'imported' => $counter]);
});