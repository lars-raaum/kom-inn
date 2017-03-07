<?php

require_once 'base.php';
require_once 'tasks/StaleMatches.php';

(new crons\tasks\StaleMatches($app))->task();
