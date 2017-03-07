<?php

require_once 'base.php';
require_once 'tasks/StaleMatches.php';

ini_set("error_log", "/tmp/crons.log"); // Stop stdout from geting stderr when run as CLI

(new crons\tasks\StaleMatches($app))->task();
