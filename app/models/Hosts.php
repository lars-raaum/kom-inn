<?php

namespace app\models;

use app\exceptions\ApiException;
use DateTime;

class Hosts implements \Pimple\ServiceProviderInterface
{
    /**
     * @var \Silex\Application
     */
    protected $app;

    /**
     * Registers this model in the app and gives it access to @app
     *
     * @param \Pimple\Container $app
     */
    public function register(\Pimple\Container $app)
    {
        $this->app = $app;
        $app['hosts'] = $this;
    }

    /**
     * Returns a single Host
     *
     * @param int $id
     * @return array|false
     * @throws ApiException if not found
     */
    public function get(int $id)
    {
        if ($id === 0) throw new ApiException("Id not valid", 404);

        $args = [$id];
        $sql = "SELECT people.*, hosts.id AS `host_id` FROM people, hosts WHERE people.id = hosts.user_id AND people.id = ?";
        error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        $host = $this->app['db']->fetchAssoc($sql, $args);
        if (!$host) {
            throw new ApiException("Host $id not found", 404);
        }
        $host['type'] = People::TYPE_HOST;
        return $host;
    }

    /**
     * Fnd all active hosts
     *
     * @return array
     */
    public function find() : array
    {
        $args = [People::STATUS_ACTIVE];
        $sql = "SELECT people.*, hosts.id AS `host_id` FROM people, hosts WHERE people.id = hosts.user_id AND people.status = ?";

        error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        $hosts = $this->app['db']->fetchAll($sql, $args);
        return $hosts;
    }

    /**
     * Create host record for person
     *
     * @param array $data
     * @return int pk
     */
    public function insert(array $data)
    {
        $dtt = \Doctrine\DBAL\Types\Type::getType('datetime');
        $types = ['updated' => $dtt, 'created' => $dtt];
        $data['updated'] = new DateTime('now');
        $data['created'] = new DateTime('now');
        $result = $this->app['db']->insert('hosts', $data, $types);
        if (!$result) {
            return false;
        }
        $id = $this->app['db']->lastInsertId();
        return $id;
    }

    /**
     * Find hosts that are useful for a guest
     *
     * @param int $guest_id
     * @param float $distance_in_km range in km away from guest that we should look for host
     * @param array $filters
     * @return array
     * @throws \Exception
     * @throws ApiException if not found
     */
    public function findHostForGuest(int $guest_id, float $distance_in_km = 20.0, array $filters = []) : array
    {
        $distance = pow($distance_in_km * 0.539956803 / 60, 2);
        $args = [People::STATUS_ACTIVE];
        $sql = "SELECT people.*, hosts.user_id FROM people, hosts WHERE people.id = hosts.user_id AND people.status = ?";

        $target_latitude = NULL;
        $target_longitude = NULL;

        $guest = $this->app['guests']->get($guest_id);

        if (!$guest) {
            throw new ApiException("Guest $guest_id not found", 404);
        }

        $target_latitude = floatval($guest['loc_lat']);
        $target_longitude = floatval($guest['loc_long']);
        if ($target_latitude && $target_longitude) {
            $sql .=  " AND (people.loc_long - ?)*(people.loc_long - ?) + (people.loc_lat - ?)*(people.loc_lat - ?) < ? ";
            $args[] = $target_longitude;
            $args[] = $target_longitude;
            $args[] = $target_latitude;
            $args[] = $target_latitude;
            $args[] = $distance;
        }

       $children = isset($filters['children']) ? $filters['children'] : null;
        if ($children !== null) {
            if ($children == 'yes') {
                $sql .= " AND people.children <> ?";
                $args[] = 0;
            } elseif ($children == 'no') {

                $sql .= " AND people.children = ?";
                $args[] = 0;
            }
        }

        $men = isset($filters['men']) ? $filters['men'] : null;
        if ($men !== null) {
            if ($men == 'yes') {
                $sql .= ' AND people.adults_m <> ?';
                $args[] = 0;
            } elseif ($men == 'no') {
                $sql .= ' AND people.adults_m = ?';
                $args[] = 0;
            }
        }

        $women = isset($filters['women']) ? $filters['women'] : null;
        if ($women !== null) {
            if ($women == 'yes') {
                $sql .= ' AND people.adults_f <> ?';
                $args[] = 0;
            } elseif ($women == 'no') {
                $sql .= ' AND people.adults_f = ?';
                $args[] = 0;
            }
        }

        error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        $hosts = $this->app['db']->fetchAll($sql, $args);

        if ($target_longitude && $target_latitude) {
            foreach ($hosts as &$host) {
                $h_lat = $host['loc_lat'];
                $h_loc = $host['loc_long'];
                $dist = sqrt(pow($h_loc - $target_longitude, 2) + pow($h_lat - $target_latitude, 2)) * 60 / 0.539956803;
                $host['distance'] = $dist;
            }

            usort($hosts, function($a, $b) {
                return $a['distance'] > $b['distance'];
            });
        }
        foreach ($hosts as &$host) {
            $now     = new DateTime();
            $updated = new DateTime($host['created']);
            if ($updated->diff($now)->days == 0) {
                $host['waited'] = "Added today";
            } else if ($updated->diff($now)->days == 1) {
                $host['waited'] = $updated->diff($now)->days . " day";
            } else {
                $host['waited'] = $updated->diff($now)->days . " days";
            }
        }
        return $hosts;
    }
}
