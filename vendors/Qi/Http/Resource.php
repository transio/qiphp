<?php
namespace Qi\Http;

/**
 * The Qi Resource class abstracts URLs
 */
class Resource
{
    const METHOD = "method";
    const SCHEME = "scheme";
    const PORT = "port";

    const CONTROLLER = "controller";
    const ACTION = "action";
    const ARGS = "params";
    
    const EXTENSION = "extension";
    const QUERY = "query";
    const FRAGMENT = "fragment";
    
    private static $URI_PARTS = array(
        self::CONTROLLER,
        self::ACTION,
        self::ARGS,
        self::EXTENSION,
        self::QUERY,
        self::FRAGMENT
    );
    
    private $parts = array();
    
    public function __construct($controller=null, $action=null, $params=null, $fragment=null, array $query=null, $extension=null)
    {
        global $settings;
        
        $this->controller = !is_null($controller) ? $controller : $settings->controller->default[0];
        if (!is_null($action)) {
            $this->action = $action;
        } else {
            // Default actions implementation
            if (!empty($settings->module->defaultActions)) {
                foreach ($settings->module->defaultActions as $match => $actions) {
                    if ((!strlen($match) || strpos($this->module, $match) !== false) && count($actions) >= 2) {
                        $this->action = $this->id > 0 ? $actions[1] : $actions[0];
                        break;
                    }
                }
            } else {
                // TODO - add default action option to settings?
                $action = "index";
            }
        }
        if (!is_null($extension)) $this->extension = $extension;
        $this->fragment = strlen($fragment) ? $fragment : null;
        if (!empty($params)) {
            $this->params = $params;
        } else {
            $this->params = array();
        }
    }
    
    public function __toString()
    {
        return $this->current();
    }
    
    public function go()
    {
        HttpResponse::redirect($this);
    }
    
    public function &set($part, $value)
    {
        $this->$part = $value;
        return $this;
    }
    
    public function &setParam($name, $value)
    {
        if (empty($this->parts["params"])) {
            $this->parts["params"] = array();
        }
        $this->parts["params"][$name] = $value;
        return $this;
    }
    
    public function setMethod($value)
    {
        $this->parts[self::METHOD] = $value;
        return $this;
    }
    
    public function getMethod()
    {
        return $this->parts[self::METHOD];
    }
    
    public function setScheme($value)
    {
        $this->parts[self::SCHEME] = $value;
        return $this;
    }
    
    public function getScheme()
    {
        return $this->parts[self::SCHEME];
    }
    
    public function setPort($value)
    {
        $this->parts[self::PORT] = (int) $value;
        return $this;
    }
    
    public function getPort()
    {
        return $this->parts[self::PORT];
    }
    
    public function setController($value)
    {
        $this->parts[self::CONTROLLER] = $value;
        return $this;
    }
    
    public function getController()
    {
        return $this->parts[self::CONTROLLER];
    }
    
    public function setAction($value)
    {
        $this->parts[self::ACTION] = $value;
        return $this;
    }
    
    public function getAction()
    {
        return $this->parts[self::ACTION];
    }
    
    public function setArgs(array $value)
    {
        $this->parts[self::ARGS] = $value;
        return $this;
    }
    
    public function setArg($value)
    {
        if (empty($this->parts[self::ARGS])) $this->parts[self::ARGS] = $array;
        $this->parts[self::ARGS][] = $value;
        return $this;
    }
    
    public function getArgs()
    {
        return $this->parts[self::ARGS];
    }
    
    public function setExtension($value)
    {
        $this->parts[self::EXTENSION] = $value;
        return $this;
    }
    
    public function getExtension()
    {
        return $this->parts[self::EXTENSION];
    }
    
    public function setQueryString(array $value)
    {
        $this->parts[self::QUERY] = $value;
        return $this;
    }
    
    public function setQueryStringParameter($key, $value)
    {
        if (empty($this->parts[self::QUERY])) $this->parts[self::QUERY] = $array;
        $this->parts[self::QUERY][$key] = $value;
        return $this;
    }
    
    public function getQueryString()
    {
        return $this->parts[self::QUERY];
    }
    
    public function setFragment($value)
    {
        $this->parts[self::FRAGMENT] = $value;
        return $this;
    }
    
    public function getFragment()
    {
        return $this->parts[self::FRAGMENT];
    }
    
    
    /**
     * Parse a Uri Object from a Striing
     * @return Uri
     * @param $resource String - a 
     */
    public static function parse($url)
    {
        $urlParts = parse_url($url);
        
        // Get the Base Resource
        $resource = $urlParts["path"];
        if (substr($resource, 0, 1) == "/") {
            $resource = substr($resource, 1);
        }
        
        // Parse the Querystring
        $query = isset($urlParts["query"]) ? $urlParts["query"] : "";
        $params = array();
        if (strlen($query)) {
            $query = explode("&", $query);
            foreach ($query as $param) {
                $param = explode("=", $param);
                $params[$param[0]] = $param[1];
            }
        }
        
        // Get the Fragment
        $fragment = isset($urlParts["fragment"]) ? $urlParts["fragment"] : null;
        
        
        // Parse the Extension from the Resource
        $extension = null;
        if (strripos($resource, ".") > strripos($resource, "/")) {
            $extension = substr($resource, strripos($resource, ".") + 1);
            $resource = substr($resource, 0, strripos($resource, "."));
        } else {
            //$extension = "html";
        }
        $module = null;
        $action = null;
        // Parse the Module / Action / Id from the Resource
        $resourceParts = explode("/", $resource);
        if (count($resourceParts) > 0 && (count($resourceParts) > 1 || $resourceParts[0] != "")) {
            // Get the Action
            // Get the Id if it exists
            if (is_numeric($resourceParts[count($resourceParts)-1])) {
                $id = $resourceParts[count($resourceParts)-1];
                unset($resourceParts[count($resourceParts)-1]);
            }
            
            $action = $resourceParts[count($resourceParts)-1];
            unset($resourceParts[count($resourceParts)-1]);
            
            
            // Get the Module
            $module = implode("/", $resourceParts);
        }
        
        // Return a new Uri object
        if(!isset($id)) $id = null;
        return new Uri($module, $id, $action, $fragment, $params, $extension);
    }
    
    /**
     * Returns the Current Uri as a String
     * @return String the uri
     */
    public function format()
    {
        $resource = array(
            $this->parts[self::CONTROLLER],
            $this->parts[self::ACTION]
        );

        if (!empty($this->parts[self::ARGS]))
            $resource = array_merge($resource, $this->parts[self::ARGS]);

        $resource = implode("/", $resource);
        
        if (!empty($this->parts[self::EXTENSION]))
            $resource .= "." . $this->parts[self::EXTENSION];
            
        if (!empty($this->parts[self::QUERY])) {
            $params = $this->parts[self::QUERY];
            $keys = array_keys($params);
            for ($i = 0; $i < count($keys); $i++) {
                $params[$keys[$i]] = urlencode($keys[$i])."=".urlencode($params[$i]));
            }
            $resource .= "?" . implode("&", $params);
        }
        
        if (!empty($this->parts[self::FRAGMENT]))
            $resource .= "#" . $this->parts[self::FRAGMENT];
            
        return "/" . $resource;
    }
    
    
    /**
     * Check if the Uri equals a given Uri
     * @return boolean
     * @param $resource Resource
     * @param $compareParams Boolean
     */
    public function equals(Resource $resource, $compareParams=false)
    {
        return $this->module == $resource->module 
            && $this->action == $resource->action 
            && $this->id == $resource->id
            && $this->fragment == $resource->fragment
            && (!$compareParams || $this->paramsEqual($resource));
    }
    
    private function paramsEqual(Uri $resource)
    {
        $equal = count($this->params) == count($resource->params);
        if (!$equal) return false;
        if (!is_array($this->params)) return !is_array($resource->params) || count($resource->params) == 0;
        foreach ($this->params as $key => $value) {
            $equal = $equal && isset($resource->params[$key]) && $resource->params[$key] == $value;
        }
        return $equal;
    }
    
    /**
     * Check if the Uri matches the pattern given
     * @return boolean
     * @param $pattern String
     */
    public function match($pattern)
    {
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = str_replace('\\/', '\/', $pattern);
        $pattern = "/{$pattern}/";
        return preg_match($pattern, $this->__toString());
    }
}
