<?php
namespace Qi;

/**
 * Configuration class
 */
class Config
{
    private $_values;
    
    public function __construct()
    {
        $this->_values = array();
    }
    
    public function &write($key, $value)
    {
        $this->_values[$key] = $value;
        return $this;
    }
    
    public function &read($key)
    {
        return isset($this->_values[$key]) ? $this->_values[$key] : null;
    }
    
}
