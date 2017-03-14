<?php

require_once 'base.php';
require_once 'tasks/PurgeInactive.php';

(new crons\tasks\PurgeInactive($app))->task();

$app['logger']->debug("CRON ENDED : " . $argv[0]);
