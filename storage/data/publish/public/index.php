<?php

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../files/<<<--publish_type-->>>/view'));

header('Vary: User-Agent,Cookie');

error_reporting(0);

require_once APPLICATION_PATH . '/FrontController.php';

$application = new Front_Controller();
$application->run();