<?php

// Define Directory Separator
define('DS', DIRECTORY_SEPARATOR);

// Register Vendor Autoloader
spl_autoload_register(function($class) {
    $parts = explode('\\', $class);
    $class = implode(DS, $parts);
    require  dirname(dirname(dirname(__FILE__))) . "vendors" . DS . "{$class}.php";
});

// Load the Config
require_once(dirname(dirname(__FILE__)) . "/local.php");

// Build the Application
$application = new \Qi\Application($config);

// Run it
$application->run();

