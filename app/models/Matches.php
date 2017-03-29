<?php

namespace app\models;

use app\exceptions\ApiException;
use app\Geo;
use DateTime;
use Doctrine\DBAL\Types\Type;

class Matches implements \Pimple\ServiceProviderInterface
{
    const STATUS_DELETED = -1;
    const STATUS_NEW = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_EXECUTED = 2;

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
        $app['matches'] = $this;
    }

    /**
     * Returns a single Guest
     *
     * @param int $id
     * @param bool $with_host
     * @param bool $with_guest
     * @return array|false
     * @throws ApiException if not found
     */
    public function get(int $id, bool $with_host = true, bool $with_guest = true)
    {
        if ($id === 0) throw new ApiException("Id not valid", 404);

        $args = [$id];
        $sql = "SELECT * FROM matches WHERE id = ?";
        $this->app['logger']->info("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        $match = $this->app['db']->fetchAssoc($sql, $args);
        if (!$match) {
            throw new ApiException("Match $id not found", 404);
        }
        if ($with_host) {
            $match['host'] = $this->app['hosts']->get((int) $match['host_id']);
        }
        if ($with_guest) {
            $match['guest'] = $this->app['guests']->get((int) $match['guest_id']);
        }

        return $match;
    }

    /**
     * Find list of matches based on status, optionally include hosts and guests
     *
     * @param int $status
     * @param array $filters
     * @param array $options
     * @return array
     */
    public function find(int $status, array $filters = [], array $options = []) : array
    {
        $args    = [$status];
        $sql     = "SELECT * FROM matches AS m";
        $where   = "WHERE m.status = ?";

        $with_guest = $filters['guest'] ?? false;
        $with_host = $filters['host'] ?? false;

        $region = $filters['region'] ?? false;
        if ($region !== false && $region !== 'all') {
            $with_host = true; // Have to have host to do regio matching
            $target = $this->app['geo']->getTargetByRegion($region);
            $distance = Geo::distanceToRadians($target['distance_in_km']);
            $target_latitude = $target['loc_lat'];
            $target_longitude = $target['loc_long'];
            $where .=  " AND (h.loc_long - ?)*(h.loc_long - ?) + (h.loc_lat - ?)*(h.loc_lat - ?) < ? ";
            $args[] = $target_longitude;
            $args[] = $target_longitude;
            $args[] = $target_latitude;
            $args[] = $target_latitude;
            $args[] = $distance;
        }

        if ($with_host) {
            $fields = 'h.id AS h_id,h.email AS h_email,h.name AS h_name,h.phone AS h_phone,h.gender AS h_gender,h.age AS h_age,h.children AS h_children,h.adults_m AS h_adults_m,h.adults_f AS h_adults_f,h.origin AS h_origin,h.zipcode AS h_zipcode,h.address AS h_address,h.status AS h_status,h.freetext AS h_freetext,h.bringing AS h_bringing,h.loc_long AS h_loc_long,h.loc_lat AS h_loc_lat,h.visits AS h_visits,h.updated AS h_updated,h.created AS h_created';
            $joins = ' LEFT JOIN people AS h ON m.host_id = h.id';
            if ($with_guest) {
                $fields .= ', g.id AS g_id, g.email AS g_email, g.name AS g_name, g.phone AS g_phone, g.gender AS g_gender, g.age AS g_age, g.children AS g_children, g.adults_m AS g_adults_m, g.adults_f AS g_adults_f, g.origin AS g_origin, g.zipcode AS g_zipcode, g.address AS g_address, g.status AS g_status, g.freetext AS g_freetext, g.bringing AS g_bringing, g.loc_long AS g_loc_long, g.loc_lat AS g_loc_lat, g.visits AS g_visits, g.updated AS g_updated, g.created AS g_created, ge.food_concerns AS g_food_concerns';
                $joins .= ' LEFT JOIN people AS g ON m.guest_id = g.id LEFT JOIN guests as ge ON m.guest_id = ge.user_id';

            }
            $sql = "SELECT m.*, {$fields} FROM matches AS m{$joins}";
        }

        $options = $options + ['limit' => 100, 'page' => 1, 'sort' => 'id DESC'];
        $limit   = $options['limit'];
        $offset  = ($options['page'] - 1) * $limit;
        $sort    = $options['sort'];
        $sql    .= " {$where} ORDER BY {$sort} LIMIT {$offset}, {$limit}";

        $this->app['logger']->info("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        $matches = $this->app['db']->fetchAll($sql, $args);

        if ($with_guest || $with_host) {
            foreach ($matches as $k => $match) {
                try {
                    if ($with_host) {
                        $host = [];
                        foreach ($match as $key => $value) {
                            if (substr($key, 0, 2) == 'h_') {
                                $host_key = substr($key, 2);
                                $host[$host_key] = $value;
                                unset($matches[$k][$key]);
                            }
                        }
                        $matches[$k]['host'] = $host;
                    }
                    if ($with_guest) {
                        $guest = [];
                        foreach ($match as $key => $value) {
                            if (substr($key, 0, 2) == 'g_') {
                                $guest_key = substr($key, 2);
                                $guest[$guest_key] = $value;
                                unset($matches[$k][$key]);
                            }
                        }
                        $matches[$k]['guest'] = $guest;
                    }
                } catch(\app\exceptions\ApiException $e) {
                    unset($matches[$k]);
                }
            }
        }

        return $matches;
    }

    /**
     * Find list of matches based on people id
     *
     * @param int $people_id
     * @return array
     */
    public function findByPeopleId($people_id)
    {
        $args = [$people_id, $people_id];
        $sql = "SELECT * FROM matches WHERE host_id = ? OR guest_id = ? ORDER BY id DESC";
        error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        return $this->app['db']->fetchAll($sql, $args);
    }

    /**
     * Insert a new match
     *
     * @param array $data
     * @return int pk
     */
    public function insert(array $data)
    {
        $now = new DateTime('now');
        $data['updated'] = $now;
        $data['created'] = $now;
        $dtt = Type::getType('datetime');
        $types = ['updated' => $dtt, 'created' => $dtt];

        $this->app['logger']->info("INSERT match Guest[{$data['guest_id']}] Host[{$data['host_id']}] by [{$this->app['PHP_AUTH_USER']}]");
        $result = $this->app['db']->insert('matches', $data, $types);
        if (!$result) {
            error_log("ERROR: Failed to insert new match!");
            return false;
        }

        $id = $this->app['db']->lastInsertId();
        return $id;
    }

    /**
     * Update match
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data) : bool
    {
        $types = ['updated' => Type::getType('datetime')];
        $data['updated'] = new DateTime('now');

        $this->app['logger']->info("UPDATE Match[{$id}] by [{$this->app['PHP_AUTH_USER']}]");
        $result = $this->app['db']->update('matches', $data, ['id' => $id], $types);
        if (!$result) {
            // @TODO grab sql error to log?
            error_log("ERROR: Failed to update match {$id}");
            return false;
        }
        return true;
    }

    /**
     * Soft delete match
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id) : bool
    {
        $types = ['updated' => Type::getType('datetime')];
        $data  = [
            'status'  => Matches::STATUS_DELETED,
            'updated' => new DateTime('now')
        ];
        $this->app['logger']->info("SOFT DELETE Match[{$id}] by [{$this->app['PHP_AUTH_USER']}]");
        $result = $this->app['db']->update('matches', $data, ['id' => $id], $types);
        if (!$result) {
            error_log("ERROR: Failed to update match");
            return false;
        }
        return true;
    }
}
