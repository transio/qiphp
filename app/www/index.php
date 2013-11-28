<?php

// Load the Bootstrapper
require_once(dirname(dirname(__FILE__)) . "/bootstrap.php");

// Load the Config
require_once(dirname(dirname(__FILE__)) . "/config.php");
require_once(dirname(dirname(__FILE__)) . "/local.php");

// Build the Application
$application = new \Qi\Application($config);

// Run it
$application->run();

