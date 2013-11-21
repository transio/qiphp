<?php
namespace Qi\Http;
-
/**
 * The Qi Session class abstracts PHP's Session collection
 */
class Server
{
    const REMOTE_ADDR = "REMOTE_ADDR";
    const SERVER_PORT = "SERVER_PORT";
    const SERVER_PROTOCOL = "SERVER_PROTOCOL";
    const HTTPS = "HTTPS";
    const PHP_AUTH_USER = "PHP_AUTH_USER";
    const PHP_AUTH_PW = "PHP_AUTH_PW";
    const SERVER_NAME = "SERVER_NAME";
    const SCRIPT_NAME = "SCRIPT_NAME";
    const PATH_INFO = "PATH_INFO";
    const QUERY_STRING = "QUERY_STRING";
    const CONTENT_TYPE = "CONTENT_TYPE";
    const CONTENT_LENGTH = "CONTENT_LENGTH";
    const HTTP_ACCEPT = "HTTP_ACCEPT";
    
    private function __construct() {}

    public function get($paramName, $defaultValue = null)
    {
        return isset($_SERVER[$paramName]) && $_SERVER[$paramName] 
            ? $_SERVER[$paramName]
            : $defaultValue;
    }
    
    public static function getRemoteAddr()
    {
        return $this->get(self::REMOTE_ADDR);
    }
    
    public function getUserAgent() {
        return $this->get(self::HTTP_USER_AGENT);
    }
    
    public static function getRequestMethod()
    {
        if isset($_SERVER["REQUEST_METHOD"]) {
            // Override the HTTP method with POST method if provided
            // This is to support a full REST implementation for browsers
            // That only support POST and GET
            if ($_SERVER["REQUEST_METHOD"] = RequestMethod::POST 
                    && isset($_POST["method"])
                    && RequestMethod::hasConstant($_POST["method"])) {
                return $_POST["method"];
            } 
            return $_SERVER["REQUEST_METHOD"];
        }
        return RequestMethod::GET;
    }
    
    public static function getHttpAccept()
    {
        return explode(";", $this->get(self::HTTP_USER_AGENT));
    }
    
    public static function accepts($type)
    {
        return in_array($type, self::getHttpAccept());
    }
    
    public static function acceptsAny(array $types)
    {
        foreach ($types as $type) {
            if (self::acceptsType($type))
                return true;
        }
        return false;
    }
    
    public static function acceptsAll(array $types)
    {
        foreach ($types as $type) {
            if (!self::acceptsType($type))
                return false;
        }
        return true;
    }
}
