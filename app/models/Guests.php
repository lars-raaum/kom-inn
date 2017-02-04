<?php

namespace app\models;

use DateTime;

class Guests implements \Pimple\ServiceProviderInterface
{

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
     * @return array
     */
    public function get(int $id)
    {
        $args = [$id];
        $sql = "SELECT people.*, guests.id AS `guest_id`, guests.food_concerns FROM people, guests WHERE people.id = guests.user_id AND people.id = ?";
        error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
        $guest = $this->app['db']->fetchAssoc($sql, $args);
        if (!$guest) {
            return false;
        }
        $guest['type'] = People::TYPE_GUEST;
        return $guest;
    }

    /**
     * Look up all guests by status, optionall filter in/out `men`, `women` and `children`. Ordered by last updated
     *
     * @TODO Add pagination
     * @param int $status
     * @return array
     */
    public function find(int $status, array $filters = [])
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
            } elseif ($men == 'no') {
                $sql .= ' AND people.adults_f = ?';
                $args[] = 0;
            }
        }

        $sql .= " ORDER BY updated DESC";

        error_log("SQL [ $sql ] [" . join(', ', $args) . "] - by [{$_SERVER['PHP_AUTH_USER']}]");
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

    public function update(int $id, array $data)
    {

        $types = ['updated' => \Doctrine\DBAL\Types\Type::getType('datetime')];
        $data['updated'] = new \DateTime('now');
        error_log("Update guest {$id} - by [{$_SERVER['PHP_AUTH_USER']}]");
        $result = $this->app['db']->update('guests', $data, ['id' => $id], $types);

        if (!$result) {
            error_log("Failed to update guest {$person['guest_id']}");
            return false;
        }
        return true;
    }
}
