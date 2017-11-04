<?php

require_once 'base.php';
require_once 'tasks/UpdatePeople.php';

(new crons\tasks\UpdatePeople($app))->task();

$app['logger']->debug("CRON ENDED : " . $argv[0]);
