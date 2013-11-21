<?php
namespace Qi\Http;

/**
 * The Qi Session class abstracts PHP's Session collection
 */
class Session
{
    private function __construct() {}

    /**
     * Start a PHP session
     */
    public static function start()
    {
        if (!session_id()) session_start();
    }
    
    /**
     * Get the current session id
     * @return String current session id
     */
    public static function getId()
    {
        self::start();
        return session_id();
    }
    
    /**
     * Change the session id
     * @return null
     * @param $id String new session id
     */
    public static function setId($id)
    {
        self::start();
        session_id($id);
    }
    
    /**
     * Auto-generate a new session id
     * @return new session id
     */
    public static function newId()
    {
        self::start();
        session_regenerate_id();
        return session_id();
    }
    
    /**
     * Get the current session name
     * @return current session name
     */
    public static function getName()
    {
        self::start();
        return session_name();
    }
    
    /**
     * Change the session name
     * @return String old session name
     * @param $name String new session name
     */
    public static function setName($name)
    {
        self::start();
        return session_name($name);
    }
    
    /**
      * Retrieves a value from the session cookies.
      * @param $key string reference to a session variable
      * @return pointer to object or variable stored as a session variable
      */
    public static function getParameter($key)
    {
        self::start();
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            if (is_string($value)) {
                $value = unserialize($value);    
            }
            return $value;
        } else {
            return null;
        }
    }

    /**
      * Stores a value as a session cookie.
      * @param $key string reference to a session variable
      * @param $value value to store as a session variable
      */
    public static function setParameter($key, $value)
    {
        self::start();
        $_SESSION[$key] = serialize($value);
    }
    
    /**
      * Clears a session cookie.
      * @param $key string reference to the session variable to clear
      */
    public static function removeParameter($key)
    {
        self::start();
        unset($_SESSION[$key]);
    }
    
    
    /**
     * Serialize session data into a savable string
     * @return 
     */
    public static function getData()
    {
        return serialize($_SESSION);
    }
    
    /**
     * Restore session from a serialized string
     * @return 
     * @param $data Object
     */
    public static function setData($data)
    {
        $data = unserialize($data);
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                self::setParameter($key, $value);
            }
        }
    }
    
    public static function keyExists($key)
    {
        return isset($_SESSION[$key]);
    }
    
    public static function restart()
    {
        session_unset();
        session_start();
        Cookie::setParameter("PHPSESSID", self::newId(), 1);
    }
}
