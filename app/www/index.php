<?php

// Load the Bootstrapper
require_once(dirname(dirname(__FILE__)) . "/bootstrap.php");

// Build the Application
$application = new \Qi\Application($config);

// Run it
$application->run();

