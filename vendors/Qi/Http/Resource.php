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
    const ARGS = "args";
    
    const EXTENSION = "extension";
    const QUERY = "query";
    const FRAGMENT = "fragment";
    
    public static $URI_PARTS = array(
        self::CONTROLLER,
        self::ACTION,
        self::ARGS,
        self::EXTENSION,
        self::QUERY,
        self::FRAGMENT
    );
    
    private $_parts = array();
    
    public static function parse()
    {
        $resource = new Resource('');
        foreach (self::$URI_PARTS as $part) {
            if (isset($_GET[$part]) && !empty($_GET[$part])) {
                $value = $_GET[$part];
                switch ($part) {
                    case self::ARGS:
                        $value = explode("/", $value);
                        break;
                }
                $resource->set($part, $value);
            }
        }
        
        print_r($resource);
        
        // Set the querystring part
        $value = $_GET;
        foreach (self::$URI_PARTS as $part) {
            if (isset($value[$part]))
                unset($value[$part]);
        }
        if (!empty($value)) $resource->set(self::QUERY, $value);
        
        return $resource;
    }
    
    public function __construct($controller, $action=null, array $args=array(), $fragment=null, array $query=null, $extension=null)
    {
        $this->_parts[self::CONTROLLER] = $controller;
        $this->_parts[self::ACTION] = empty($action) ? "index" : $action;
        $this->_parts[self::ARGS] = empty($args) ? array() : $args;
        $this->_parts[self::EXTENSION] = empty($extension) ? null : $extension;
        $this->_parts[self::FRAGMENT] = empty($fragment) ? null : $fragment;
        $this->_parts[self::QUERY] = empty($query) ? array() : $query;
    }
    
    /**
     * Returns the Current Uri as a String
     * @return String the uri
     */
    public function __toString()
    {
        $resource = array(
            $this->_parts[self::CONTROLLER],
            $this->_parts[self::ACTION]
        );

        if (!empty($this->_parts[self::ARGS]))
            $resource = array_merge($resource, $this->_parts[self::ARGS]);

        $resource = implode("/", $resource);
        
        if (!empty($this->_parts[self::EXTENSION]))
            $resource .= "." . $this->_parts[self::EXTENSION];
            
        if (!empty($this->_parts[self::QUERY])) {
            $query = $this->_parts[self::QUERY];
            $keys = array_keys($query);
            for ($i = 0; $i < count($keys); $i++) {
                $query[$keys[$i]] = urlencode($keys[$i])."=".urlencode($query[$i]);
            }
            $resource .= "?" . implode("&", $query);
        }
        
        if (!empty($this->_parts[self::FRAGMENT]))
            $resource .= "#" . $this->_parts[self::FRAGMENT];
            
        return "/" . $resource;
    }
    
    public function go()
    {
        HttpResponse::redirect($this);
    }
    
    public function set($part, $value)
    {
        $this->_parts[$part] = $value;
        return $this;
    }
    
    public function setParam($name, $value)
    {
        if (empty($this->_parts[self::QUERY])) {
            $this->_parts[self::QUERY] = array();
        }
        $this->_parts[self::QUERY][$name] = $value;
        return $this;
    }
    
    public function setMethod($value)
    {
        $this->_parts[self::METHOD] = $value;
        return $this;
    }
    
    public function getMethod()
    {
        return $this->_parts[self::METHOD];
    }
    
    public function setScheme($value)
    {
        $this->_parts[self::SCHEME] = $value;
        return $this;
    }
    
    public function getScheme()
    {
        return $this->_parts[self::SCHEME];
    }
    
    public function setPort($value)
    {
        $this->_parts[self::PORT] = (int) $value;
        return $this;
    }
    
    public function getPort()
    {
        return $this->_parts[self::PORT];
    }
    
    public function setController($value)
    {
        $this->_parts[self::CONTROLLER] = $value;
        return $this;
    }
    
    public function getController()
    {
        return $this->_parts[self::CONTROLLER];
    }
    
    public function setAction($value)
    {
        $this->_parts[self::ACTION] = $value;
        return $this;
    }
    
    public function getAction()
    {
        return $this->_parts[self::ACTION];
    }
    
    public function setArgs(array $value)
    {
        $this->_parts[self::ARGS] = $value;
        return $this;
    }
    
    public function setArg($value)
    {
        if (empty($this->_parts[self::ARGS])) $this->_parts[self::ARGS] = $array;
        $this->_parts[self::ARGS][] = $value;
        return $this;
    }
    
    public function getArgs()
    {
        return $this->_parts[self::ARGS];
    }
    
    public function setExtension($value)
    {
        $this->_parts[self::EXTENSION] = $value;
        return $this;
    }
    
    public function getExtension()
    {
        return $this->_parts[self::EXTENSION];
    }
    
    public function setQueryString(array $value)
    {
        $this->_parts[self::QUERY] = $value;
        return $this;
    }
    
    public function setQueryStringParameter($key, $value)
    {
        if (empty($this->_parts[self::QUERY])) $this->_parts[self::QUERY] = $array;
        $this->_parts[self::QUERY][$key] = $value;
        return $this;
    }
    
    public function getQueryString()
    {
        return $this->_parts[self::QUERY];
    }
    
    public function setFragment($value)
    {
        $this->_parts[self::FRAGMENT] = $value;
        return $this;
    }
    
    public function getFragment()
    {
        return $this->_parts[self::FRAGMENT];
    }
    
    /**
     * Check if the Uri equals a given Uri
     * @return boolean
     * @param $resource Resource
     * @param $compareQueryString Boolean
     */
    public function equals(Resource $resource, $compareQueryString=false)
    {
        return $this->_parts[self::CONTROLLER] == $resource->_parts[self::CONTROLLER]
            && $this->_parts[self::ACTION] == $resource->_parts[self::ACTION]
            && $this->_parts[self::ARGS] == $resource->_parts[self::ARGS]
            && (!$compareQueryString || $this->_parts[self::QUERY] == $resource->_parts[self::QUERY]);
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
