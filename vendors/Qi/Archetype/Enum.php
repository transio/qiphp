<?php
namespace Qi\Archetype;

/**
 * Dynamic Enum Class
 * This is to be used to create a pseudo-enum in PHP for stricter typing of enumerables
 * This enum class serves as an Archetype for all derived Enums
 * @author Steven Moseley
 */
abstract class Enum
{
    protected $value;
    
    public function __construct($value)
    {
        $this->value = $value;
    }
    
    public function __toString()
    {
        //return get_class($this)."({$this->value})";
        return (string) $this->value;
    }
    
    public static function __callStatic($name, $args)
    {
        $class = get_called_class();
        if (defined("{$class}::{$name}")) {
            $value = constant("{$class}::{$name}");
            return new $class($value);
        }
    }
    
    public function equals(\Qi\Archetype\Enum $enum)
    {
        return $enum->__toString()  == $this->__toString();
        //&& get_class($enum) == get_class($this);
    }

    public function getValue()
    {
        return $this->value;
    }
    
    public static function hasConstant($value)
    {
        $class = new ReflectionClass(get_called_class());
        var_dump($class->hasConstant($value));
    }
}
