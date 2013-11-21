<?php
namespace Qi\Http;

/**
 * The Qi Cookie class abstracts PHP's Cookie collection
 */
class Cookie
{
    private function __construct() {}

    public static function setParameter($key, $value, $daysUntilExpires=365, $path="/", $domain=null)
    {
        if (is_object($value) || is_array($value)) {
            $value = serialize($value);
        }
        $expires = time() + 60 * 60 * 24 * $daysUntilExpires;
        setcookie($key, $value, $expires, $path, $domain);
    }

    public static function getParameter($key, $path="/", $domain=null)
    {
        if (array_key_exists($key, $_COOKIE)) {
            $value = str_replace("\n","",stripslashes($_COOKIE[$key]));
            //$obj = strlen($value) > 2 ? unserialize($value) : "";
            $obj = isset($value) ? unserialize($value) : "";
            if (is_object($obj) || is_array($obj)) {
                $value =& $obj;
            }
            return $value;
        } else {
            return null;
        }
    }
    
    public static function removeParameter($key, $path="/", $domain=null)
    {
        $value = self::getParameter($key, $path, $domain);
        setcookie($key, $value, 0, $path, $domain); // Necessary?
        setcookie($key, null, 0, $path, $domain);
    }
}
