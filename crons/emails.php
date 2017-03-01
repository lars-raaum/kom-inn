<?php

require_once 'base.php';
require_once 'tasks/EmailsTask.php';

ini_set("error_log", "emails.log"); // Stop stdout from geting stderr when run as CLI

/** @var \app\Cli $app */
$app->run([new crons\tasks\EmailsTask($app), 'task']);
