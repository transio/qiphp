<?php
namespace Qi\Security;

/**
 * The Security class is a utility for authorizing users in a Qi application
 */
class Security
{
    /**
     * Private constructor to prevent instantiation
     */
    private function __construct() {}
    
    /**
     * Authorizes a user to perform a specified module/[id]/[action]
     * If the user is not authorized, sends a redirect to the login module
     * @return null
     * @param $archetype Archetype
     * @param $module String
     * @param $id int[optional]
     * @param $action String[optional]
     * @param $extra String[optional]
     */
    public static function authorize(Archetype &$archUser=null, Uri &$uri=null)
    {
        // Get global settings
        global $settings;
        
        // Get the predefined group-field from settings
        $groupField = $settings->security->authClassGroupField;
        
        // Get the uri as a string
        $url = $uri->__toString();
        
        // Default authorized
        $authorized = true;
        foreach ($settings->security->permissions as $pattern => $roles) {
            // Set up the Preg Match pattern
            $pattern = "/{$pattern}/";
            
            // If the module matches the pattern of a restricted resource
            if (preg_match($pattern, $url)) {
                $authorized = false;
                // If $user is a proper user, check permissions to this resource
                if ($archUser instanceof $settings->security->authClass) {
                    foreach ($roles as $role /* TODO  => $permissionLevel */) {
                        // If the user's group is authorized, allow access
                        if ($role == $archUser->$groupField) {
                            $authorized = true;
                            break;
                        }
                    }
                }
            }
        }
        
        // If user unauthorized for this resource, redirect to authorization module
        if (!$authorized) {
            if (!$GLOBALS["uri"]->equals($settings->security->authModule))
                header("Location: {$settings->security->authModule}?redirect=".$uri->current());
        }
    }
    
    /**
     * Gets the currently logged-in user
     * @return Archetype
     */
    public static function getUser()
    {
        // Get global settings
        global $settings;
        
        $userData = Session::getParameter($settings->security->authClass);
        if (is_array($userData)) {
            return new $settings->security->authClass($userData, DataSource::DATA);
        } else {
            return null;
        }
    }
    
    /**
     * Sets the currently logged-in user to the Session
     * @return null
     * @param $user Archetype
     */
    public static function setUser($user)
    {
        // Get global settings
        global $settings;
        
        if (get_class($user) == $settings->security->authClass) {
            Session::setParameter($settings->security->authClass, $user->getData());
        }
    }
    
    public static function unsetUser()
    {
        Session::removeParameter($GLOBALS["settings"]->security->authClass);
    }
    
    /**
     * Persists the user to a Cookie
     * @return 
     * @param $user Object
     */
    public static function saveUser($user)
    {
        // Get global settings
        global $settings;
        $cookie = $settings->security->userCookie;
        Cookie::setParameter("qi_security_user", $user->$cookie);
    }
    
    /**
     * Loads the persisted user from Cookie
     * @return 
     */
    public static function loadUser()
    {
        // Get global settings
        global $settings;
        
        // TODO - Revise this using Cookie class
        /*
        $user = new User();
        // Cookie exists -> anonymous or saved user exists
        if (isset($_COOKIE["user"])) {
            list($cookieValue, $userId) = explode( "::", $_COOKIE["user"] );
            if ($userId == '0') {
                // anonymous user
                $user->user_cookie = $cookieValue;
            }else{    // saved user
                
                foreach ($userIterator as $result) {
                $user->id = 0;
                    $user = $result;
                }
            }
        }else{
            $user = createAnonymousUser();
        }
        return $user;
        */
    }
}    
