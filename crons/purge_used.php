<?php

require_once 'base.php';
require_once 'tasks/PurgeUsed.php';

(new crons\tasks\PurgeUsed($app))->task();

$app['logger']->debug("CRON ENDED : " . $argv[0]);
