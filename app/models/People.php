<?php

namespace app\models;

use app\exceptions\ApiException;
use DateTime;
use app\Geo;
use Doctrine\DBAL\Types\Type;

/**
 *
 *    Flows:
 *
 *    Signup > Active > Matched > Used > Deleted > Purged
 *    Signup > Active > Matched > Used > Deleted > Reactivated > Active
 *    Signup > Active > Matched > Used > Active
 *    Signup > Active > Expired > Purged
 *    Signup > Active > Expired > Reactivated > Active
 *
 */
class People implements \Pimple\ServiceProviderInterface
{
    const STATUS_PURGED = -3;
    const STATUS_EXPIRED = -2;
    const STATUS_DELETED = -1;
    const STATUS_ACTIVE = 1;
    const STATUS_USED = 2;

    const TYPE_GUEST = 'GUEST';
    const TYPE_HOST = 'HOST';

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
        $app['people'] = $this;
    }

    /**
     * Returns a single person, with host or guest data as appropriate
     *
     * @param int $id
     * @return array|false
     * @throws ApiException if not found
     */
    public function get(int $id)
    {
        if ($id === 0) throw new ApiException("Id not valid", 404);

        $args = [(int) $id];
        $sql = "SELECT p.*, g.id as `guest_id`, g.food_concerns, h.id as `host_id` FROM people AS p ".
               "LEFT JOIN guests AS g ON (p.id = g.user_id) ".
               "LEFT JOIN hosts  AS h ON (p.id = h.user_id) ".
               "WHERE p.id = ?";
        $this->app['logger']->info("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        $person = $this->app['db']->fetchAssoc($sql, $args);

        if (!$person) {
            throw new ApiException("Person $id not found", 404);
        }

        if ($person['guest_id'] === NULL) {
            unset($person['guest_id']);
            unset($person['food_concerns']);
            $person['type'] = People::TYPE_HOST;
        }
        if ($person['host_id'] === NULL) {
            unset($person['host_id']);
            $person['type'] = People::TYPE_GUEST;
        }
        return $person;
    }

    /**
     * Returns all users that match #status, paginated
     *
     * @param int|boolean $status if false, all users that is not deleted will be returned
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function find($status, int $limit = 10, int $offset = 0) : array
    {
        $sql = "SELECT p.*, g.id as `guest_id`, g.food_concerns, h.id as `host_id` ".
               "FROM people AS p LEFT JOIN guests AS g ON (p.id = g.user_id) LEFT JOIN hosts  AS h ON (p.id = h.user_id) ";
        if ($status !== false) {
            $args = [$status];
            $sql .= "WHERE status = ?";
        } else {
            $args = [People::STATUS_PURGED];
            $sql .= "WHERE status != ?";
        }
        $sql .= " ORDER BY updated DESC LIMIT {$offset}, {$limit}";

        $this->app['logger']->info("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        $people = (array) $this->app['db']->fetchAll($sql, $args);

        foreach ($people as &$person) {
            if ($person['guest_id'] === NULL) {
                unset($person['guest_id']);
                unset($person['food_concerns']);
                $person['type'] = People::TYPE_HOST;
            }
            if ($person['host_id'] === NULL) {
                unset($person['host_id']);
                $person['type'] = People::TYPE_GUEST;
            }
        }

        return $people;
    }

    /**
     * Returns a count of all people with given status
     *
     * @param int|boolean $status
     * @return int
     */
    public function total($status) : int
    {
        $sql = "SELECT COUNT(1) FROM people ";
        if ($status !== false) {
            $args = [$status];
            $sql .= "WHERE status = ?";
        } else {
            $args = [People::STATUS_PURGED];
            $sql .= "WHERE status != ?";
        }
        $this->app['logger']->info("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$this->app['PHP_AUTH_USER']}]");
        $total = $this->app['db']->fetchColumn($sql, $args, 0);

        return $total;
    }

    /**
     * Insert person
     *
     * @param array $data
     * @return int primary key of person
     */
    public function insert(array $data)
    {
        $dtt = Type::getType('datetime');
        $types = ['updated' => $dtt, 'created' => $dtt];
        $data['updated'] = new DateTime('now');
        $data['created'] = new DateTime('now');
        $data['status']  = People::STATUS_ACTIVE;

        if ($data['address']) {
            $geo = new Geo();
            try {
                $coords = $geo->getCoords($data);
                $data['loc_long'] = $coords->getLongitude();
                $data['loc_lat'] = $coords->getLatitude();
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }
        }

        $this->app['logger']->info("INSERT to People - by [{$this->app['PHP_AUTH_USER']}]");
        $result = $this->app['db']->insert('people', $data , $types);
        if (!$result) {
            return false;
        }
        $id = $this->app['db']->lastInsertId();
        return $id;
    }

    /**
     * Update person data
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data) : bool
    {
        foreach ($data as $key => $value) {
            if ($value === null) {
                unset($data[$key]);
            }
        }

        $types = ['updated' => Type::getType('datetime')];
        $data['updated'] = new DateTime('now');

        $this->app['logger']->info("UPDATE person {$id} - by [{$this->app['PHP_AUTH_USER']}]");
        $result = $this->app['db']->update('people', $data, ['id' => $id], $types);
        if (!$result) {
            error_log("ERROR: Failed to update person {$id}");
            return false;
        }

        return true;
    }

    /**
     * Updates a person's status to ACTIVE
     *
     * @param int $id
     * @return bool
     */
    public function setToActive(int $id) : bool
    {
        $data = [
            'status' => People::STATUS_ACTIVE
        ];
        return $this->update($id, $data);
    }

    /**
     * Updates a person's status to USED
     *
     * @param int $id
     * @return bool
     */
    public function setToUsed(int $id) : bool
    {
        $data = [
            'status' => People::STATUS_USED
        ];
        return $this->update($id, $data);
    }

    /**
     * Updates a person's status to DELETED
     *
     * @param int $id
     * @return bool
     */
    public function setToSoftDeleted(int $id) : bool
    {
        $data = [
            'status' => People::STATUS_DELETED
        ];
        return $this->update($id, $data);
    }

    /**
     * Updates a person's status to EXPIRED
     *
     * @param int $id
     * @return bool
     */
    public function setToExpired(int $id) : bool
    {
        $data = [
            'status' => People::STATUS_EXPIRED
        ];
        return $this->update($id, $data);
    }

    /**
     * Soft delete, but anonymize person data.
     *
     * Removes `name`, `email`, `phone`, `address`, `freetext` and  `bringing`
     *
     * @param int $id
     * @return bool
     */
    public function purge(int $id) : bool
    {
        $data  = [
            'name'      => '#DELETED#',
            'email'     => '#DELETED#',
            'phone'     => '#DELETED#',
            'address'   => '#DELETED#',
            'freetext'  => NULL,
            'bringing'  => NULL,
            'status'    => People::STATUS_PURGED,
            'updated'   => new DateTime('now')
        ];
        $types = ['updated' => Type::getType('datetime')];

        $this->app['logger']->info("SOFT DELETE Person[{$id}] by [{$this->app['PHP_AUTH_USER']}]");
        $result = $this->app['db']->update('people', $data, ['id' => $id], $types);
        if ($result == 0) {
            error_log("ERROR: Failed to update person");
            return false;
        }
        return true;
    }

    /**
     * Change a person from a guest to a host or other way around
     *
     * @param int $id
     * @return bool
     * @throws ApiException if not found
     */
    public function changeTypeOfPerson(int $id) : bool
    {
        $matches = $this->app['matches']->findByPeopleId($id);
        if ($matches) {
            throw new ApiException("Can not change type of matched person", 400);
        }

        $person = $this->get($id);
        if ($person['type'] == People::TYPE_GUEST) {
            $this->app['db']->delete('guests', ['user_id' => $id]);
            $this->app['hosts']->insert(['user_id' => $id]);
        } else {
            $this->app['db']->delete('hosts', ['user_id' => $id]);
            $this->app['guests']->insert(['user_id' => $id]);
        }
        return true;
    }
}
