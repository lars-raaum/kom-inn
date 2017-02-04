<?php

namespace app\models;

class People implements \Pimple\ServiceProviderInterface
{
    const STATUS_DELETED = -1;
    const STATUS_ACTIVE = 1;
    const STATUS_USED = 2;

    const TYPE_GUEST = 'GUEST';
    const TYPE_HOST = 'HOST';

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
     * Returns a single person, with host or guest data as approriate
     *
     * @param int $id
     * @return array
     */
    public function get(int $id)
    {
        $args = [(int) $id];
        $sql = "SELECT p.*, g.id as `guest_id`, g.food_concerns, h.id as `host_id` FROM people AS p ".
               "LEFT JOIN guests AS g ON (p.id = g.user_id) ".
               "LEFT JOIN hosts  AS h ON (p.id = h.user_id) ".
               "WHERE p.id = ?";
        error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
        $person = $this->app['db']->fetchAssoc($sql, $args);

        if (!$person) return null;
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
    public function find($status, int $limit = 10, int $offset = 0)
    {
        if ($status !== false) {
            $args = [$status];
            $sql = "SELECT * FROM people WHERE status = ? ORDER BY updated DESC LIMIT {$offset}, {$limit} ";
        } else {
            $args = [People::STATUS_DELETED];
            $sql = "SELECT * FROM people WHERE status != ? ORDER BY updated DESC LIMIT {$offset}, {$limit} ";
        }
        error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
        $people = $this->app['db']->fetchAll($sql, $args);
        return $people;
    }

    /**
     * Returns a count of all people with given status
     *
     * @param int|boolean $status
     * @return int
     */
    public function total($status)
    {
        if ($status !== false) {
            $args = [$status];
            $sql = "SELECT COUNT(1) FROM people WHERE status = ?";
        } else {
            $args = [People::STATUS_DELETED];
            $sql = "SELECT COUNT(1) FROM people WHERE status != ?";
        }
        error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
        $total = $this->app['db']->fetchColumn($sql, $args, 0);

        return $total;
    }

    /**
     * Update person data
     *
     * @param int $id
     * @param array $data
     * @return boolean
     */
    public function update(int $id, array $data)
    {
        foreach ($data as $key => $value) {
            if ($value === null) {
                unset($data[$key]);
            }
        }

        $types = ['updated' => \Doctrine\DBAL\Types\Type::getType('datetime')];
        $data['updated'] = new \DateTime('now');

        error_log("Update person {$id} - by [{$_SERVER['PHP_AUTH_USER']}]");
        $result = $this->app['db']->update('people', $data, ['id' => $id], $types);
        if (!$result) {
            error_log("Failed to update person {$id}");
            return false;
        }

        return true;
    }

    /**
     * Soft delete, but anonymize person data.
     *
     * Removes `name`, `email`, `phone`, `address`, `freetext` and  `bringing`
     *
     * @param int $int
     * @return boolean
     */
    public function delete($id)
    {
        $data  = [
            'name'      => '#DELETED#',
            'email'     => '#DELETED#',
            'phone'     => '#DELETED#',
            'address'   => '#DELETED#',
            'freetext'  => NULL,
            'bringing'  => NULL,
            'status'    => People::STATUS_DELETED
        ];

        error_log("DELETING DATA for Person[{$id}] by [{$_SERVER['PHP_AUTH_USER']}]");
        $result = $this->app['db']->update('people', $data, ['id' => (int) $id]);
        return $result === 1;
    }
}
