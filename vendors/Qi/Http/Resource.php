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
    
    public static function parse()
    {
        $resource = new Resource('');
        foreach (self::$URI_PARTS as $part) {
            if (isset($_GET[$part]) && !empty($_GET[$part])) {
                $resource->set($part, $_GET[$part]);
            }
        }
        return $resource;
    }
    
    public function __construct($controller, $action=null, array $params=array(), $fragment=null, array $query=null, $extension=null)
    {
        $this->controller = $controller;
        $this->action = is_null($action) ? "index" : $action;
        $this->extension = empty($extension) ? null : $extension;
        $this->fragment = empty($fragment) ? null : $fragment;
        $this->params = empty($params) ? array() : $params;
    }
    
    /**
     * Returns the Current Uri as a String
     * @return String the uri
     */
    public function __toString()
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
