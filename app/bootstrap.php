<?php

ini_set('error_reporting', E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set('display_errors', true);

// Define Directory Separator
define('DS', DIRECTORY_SEPARATOR);

// Define Base Path
define('BASE_PATH', dirname(dirname(__FILE__)));

// Register Vendor Autoloader
spl_autoload_register(function($class) {
    $parts = explode('\\', $class);
    $class = implode(DS, $parts);
    if (substr($class, 0, 4) != "app/")
        $class = "vendors" . DS . $class;
    require  BASE_PATH . DS . "{$class}.php";
});
