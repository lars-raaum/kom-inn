<?php

namespace app\models;

use app\exceptions\ApiException;
use DateTime;
use Doctrine\DBAL\Types\Type;

class Guests implements \Pimple\ServiceProviderInterface
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
        $app['guests'] = $this;
    }

    /**
     * Returns a single Guest
     *
     * @param int $id
     * @return array|false
     * @throws ApiException if not found
     */
    public function get(int $id)
    {
        if ($id === 0) throw new ApiException("Id not valid", 404);

        $args = [$id];
        $sql = "SELECT people.*, guests.id AS `guest_id`, guests.food_concerns FROM people, guests WHERE people.id = guests.user_id AND people.id = ?";
        $this->app['monolog']->info("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        $guest = $this->app['db']->fetchAssoc($sql, $args);
        if (!$guest) {
            throw new ApiException("Guest $id not found", 404);
        }
        $guest['type'] = People::TYPE_GUEST;
        return $guest;
    }

    /**
     * Look up all guests by status, optionall filter in/out `men`, `women` and `children`. Ordered by last updated
     *
     * @TODO Add pagination
     * @param int $status
     * @param array $filters
     * @return array
     */
    public function find(int $status, array $filters = []) : array
    {
        $args = [$status];
        $sql = "SELECT people.*, guests.user_id FROM people, guests WHERE people.id = guests.user_id AND people.status = ?";

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

        $sql .= " ORDER BY updated DESC";

        $this->app['monolog']->info("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        $guests = $this->app['db']->fetchAll($sql, $args);

        foreach ($guests as &$guest) {
            $now     = new DateTime();
            $updated = new DateTime($guest['created']);
            if ($updated->diff($now)->days == 0) {
                $guest['waited'] = "Added today";
            } else if ($updated->diff($now)->days == 1) {
                $guest['waited'] = $updated->diff($now)->days . " day";
            } else {
                $guest['waited'] = $updated->diff($now)->days . " days";
            }
        }
        return $guests;
    }

    /**
     * Insert guest record for person
     *
     * @param array $data
     * @return int pk
     */
    public function insert(array $data)
    {
        $dtt = Type::getType('datetime');
        $types = ['updated' => $dtt, 'created' => $dtt];
        $data['updated'] = new DateTime('now');
        $data['created'] = new DateTime('now');
        $result = $this->app['db']->insert('guests', $data, $types);
        if (!$result) {
            return false;
        }
        $id = $this->app['db']->lastInsertId();
        return $id;
    }

    /**
     * Update a guest data
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data) : bool
    {
        if ($id === 0) return false;

        $types = ['updated' => Type::getType('datetime')];
        $data['updated'] = new \DateTime('now');
        $this->app['monolog']->info("UPDATE guest {$id} - by [{$this->app['PHP_AUTH_USER']}]");
        $result = $this->app['db']->update('guests', $data, ['id' => $id], $types);

        if (!$result) {
            error_log("Failed to update guest {$id}");
            return false;
        }
        return true;
    }
}
