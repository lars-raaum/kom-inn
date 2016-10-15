<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('/api/importcsv/', function(Request $request) use ($app, $types) {
    $r = $request->request;

    $type = $r->get('type');

    $defaults = [
        'status'    => 1,
        'updated'   => new DateTime('now'),
        'created'   => new DateTime('now')
    ];

    $row = 1;
    if (($handle = fopen("dummydata-1.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $data = array_map("utf8_encode", $data);
            /*
             * 0 id
               1 name
               2 gender
               3 age
               4 children
               5 adults_m
               6 adults_f
               7 address
               8 zipcode
               9 origin
               10 phone
               11 email
               12 status
               13 freetext
               14 visits
               15 loc_long
               16 loc_lat
               17 visits
             */
            
            $data2 = [
                    'email'     => $data[11],
                    'name'      => $data[1],
                    'phone'     => $data[10],
                    'gender'    => $data[2] == 'm' ? 'male' : 'female',
                    'age'       => $data[3],
                    'children'  => $data[4] ?: 0,
                    'adults_m'  => $data[5] ?: 0,
                    'adults_f'  => $data[6] ?: 0,
                    'origin'    => $data[9],
                    'zipcode'   => $data[8],
                    'address'   => $data[7],
                    'freetext'  => $data[13],
                ] + $defaults;

            print_r($data2);

            if ($data2['email'] == 'email') {
                continue;
            }

            // validation

            $result = $app['db']->insert('people', $data2, $types);

            if (!$result) {
                return $app->json(['result' => false]);
            }
            $user_id = $app['db']->lastInsertId();

            $data3 = [
                'user_id' => $user_id,
                'updated' => new DateTime('now'),
                'created' => new DateTime('now')
            ];
            if ($type == 'host') {
                $result = $app['db']->insert('hosts', $data3, $types);
                $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
            } else {
                $result = $app['db']->insert('guests', $data3, $types);
                $sql = "SELECT people.*, guests.food_concerns FROM people, guests WHERE people.id = guests.user_id AND people.id = ?";
            }

            $row++;
        }
        fclose($handle);
    }



    $person = $app['db']->fetchAssoc($sql, [(int) $user_id]);

    return $app->json($person);
});