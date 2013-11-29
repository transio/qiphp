<?php
/**
 * QiPHP Bootstrapper
 * - Defines global constants
 * - Defines helper functions 
 * - Sets up vendor autoloader 
 * - Loads config files
 * - Defines fatal shutdown logging
 */
 
// Define Directory Separator
define('DS', DIRECTORY_SEPARATOR);
define('BASE_PATH', dirname(dirname(__FILE__)));

// Define a pr() function for easier debugging
function pr($string) {
    print("<pre style=\"display:block\">");
    print_r($string);
    print("</pre>");
}

// Register Vendor Autoloader
spl_autoload_register(function($class) {
    $parts = explode('\\', $class);
    $class = implode(DS, $parts);
    if (substr($class, 0, 4) != "app/")
        $class = "vendors" . DS . $class;
    require  BASE_PATH . DS . "{$class}.php";
});

// Load the Global an dlocal Configs
require_once(BASE_PATH . "/app/conf/global.php");
require_once(BASE_PATH . "/app/conf/local.php");

// Define debug mode
try {
    define("DEBUG", $config->read("app.debug"));
} catch (\Exception $e) {
    define("DEBUG", false);
}

// Set error logging based on debug settings
if (DEBUG) {
   ini_set('error_reporting', E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
   ini_set('display_errors', true);
} else {
}

// Register shutdown function
function shutdown() {
    if ($e = error_get_last()) {
        $f = fopen(BASE_PATH . DS . 'log' . DS . 'shutdown.log', 'a'); 
        fwrite($f, "$e\n"); 
        fclose($f);
    }
}
register_shutdown_function('shutdown');
