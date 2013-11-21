<?php
namespace Qi\Http;

/**
 * The Response class abstracts the HTTP Response (headers, etc.)
 */
class Response
{
    private $params = array();
    
    public function __construct()
    {
    }
    
    public static function setParameter($name, $value)
    {
        $this->params[$name] = $value;
    }
    
    public static function redirect($location, $status=302)
    {
        Header::redirect($location, $status);
    }
    
}
