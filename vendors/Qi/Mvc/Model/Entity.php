<?php
namespace Qi\Archetype;

use Qi\Archetype\Enum\AggregationType;
use Qi\Archetype\Enum\DataType;
use Qi\Archetype\Enum\DataFormat;
use Qi\Archetype\Enum\Source;

/**
 * Dynamic Entity class
 * This class is an Archetype for all Business Entity Objects you wish to define
 * @author Steven Moseley
 */
abstract class Entity implements \ArrayAccess
{
    protected static $PROPERTIES = array();

    protected static $DEFAULT_PROPERTY = array(
        // The DataType of the element
        'type'              => DataType::STRING,
        'aggregation'       => false,
        'aggregate_class'   => '\Qi\Archetype\Collection',
        'minlength'         => null,
        'maxlength'         => null,
        'nullable'          => true,
        'default'           => null
    );

    protected $_values;
    protected $_initialized;
    protected $_source;

    public function __construct(array $data = null, $source = Source::TRUSTED)
    {
        $this->_initialized = array();
        foreach (static::$PROPERTIES as $key => $properties) {
            $data_key = isset($properties['key']) ? $properties['key'] : $key;
            if (!empty($data) && isset($data[$data_key]) && (!empty($data[$data_key]) || $data[$data_key]===0)) {
                $this->$key = $data[$data_key];
            } else if (isset($properties['default'])) {
                $this->$key = $properties['default'];
            } else {
                $this->$key = null;
            }
        }
    }

    /**
     * Magic sleep method for serialization
     * @return array
     */
    public function __sleep()
    {
        return array_keys(static::$PROPERTIES);
    }

    // TODO - __wakeup method?

    /**
     * Magic toString for printing
     * @return string
     */
    public function __toString()
    {
        return get_class($this);
    }

    /**
     * Magic getter method
     * @param $key
     * @return null
     */
    public function __get($key)
    {
        if (isset(static::$PROPERTIES[$key])) {
            if (array_key_exists($key, $this->_values)) {
                // If the value hasn't been cast and initialized, then init it
                if (!isset($this->_initialized[$key])) {
                    $this->_initialize($key);
                }

                // Return the value
                return $this->_values[$key];
            } else {
                return null;
            }
        } else {
            ///throw new Exception("Invalid property requested ({$key})");
            return null;
        }
    }

    /**
     * Magic setter method
     * @param $key
     * @param $value
     * @throws Exception
     */
    public function __set($key, $value)
    {
        // Check the key, nullability, maxlength, and minlength for untrusted data sources
        if ($this->_source === Source::UNTRUSTED) {
            if (isset(static::$PROPERTIES[$key])) {
                $not_null = isset(static::$PROPERTIES[$key]['nullable']) && !static::$PROPERTIES[$key]['nullable'];
                if ($not_null && is_null($value) && !isset(static::$PROPERTIES[$key]['default'])) {
                    throw new Exception("{$key} is not nullable");
                }

                if (static::$PROPERTIES[$key]['maxlength'] && strlen($value) > static::$PROPERTIES[$key]['maxlength']) {
                    throw new Exception("{$key} exceeds maximum length of {static::$PROPERTIES[$key]['maxlength']}");
                }

                if (static::$PROPERTIES[$key]['minlength'] && strlen($value) < static::$PROPERTIES[$key]['minlength']) {
                    throw new Exception("{$key} has a minimum length of {static::$PROPERTIES[$key]['minlength']}");
                }

            } else {
                throw new Exception("Invalid property requested ({$key})");
            }
        }

        // Temporarily set value without casting it, so as to allow lazy loading
        $this->_values[$key] = $value;

        // If the key is flagged as initialized, unflag it
        if (isset($this->_initialized[$key])) unset($this->_initialized[$key]);
    }

    /**
     * Dynamic setter and getter call functionality.
     * Spoofs property get and set functionality as a function call.
     * E.g. $this->property_name becomes $this->getPropertyName() and $this->setPropertyName($value)
     *
     * @param $name
     * @param $args
     * @throws Exception
     * @return mixed
     */
    public function __call($name, $args)
    {
        // Get the words of the method called
        $class = get_class($this);
        $name = preg_replace("/([A-Z0-9]+)/", "_\\1", $name);
        $words = explode("_", strtolower($name));

        // If there are less than 2 words, it's an invalid method
        if (count($words) < 2) {
            throw new Exception("Invalid method: {$class}::{$name}");
        } else {
            // Get the method
            $method = $words[0];
            unset($words[0]);
            $property = implode('_', $words);

            if (!isset(static::$PROPERTIES[$property])) {
                //throw new Exception("Invalid method: {$class}::{$name}");
            } else {
                // Determine what method was called
                switch($method) {
                    // Getter was called
                    case 'get':
                        return $this->$property;
                        break;

                    // Setter was called
                    case 'set':
                        if (!$args[0]) throw new Exception("Required argument \$value not supplied: {$class}::{$name}");
                        $this->$property = $args[0];
                        break;
                }
            }
        }
        return null;
    }

    protected function _initialize($key)
    {
        $value = $this->_values[$key];
        $aggregation = isset(static::$PROPERTIES[$key]['aggregation']) ? static::$PROPERTIES[$key]['aggregation'] : null;

        $type = isset(static::$PROPERTIES[$key]['type']) ? static::$PROPERTIES[$key]['type'] : DataType::STRING;
        $nullable = !isset(static::$PROPERTIES[$key]['nullable']) || static::$PROPERTIES[$key]['nullable'];
        $default = isset(static::$PROPERTIES[$key]['default']) ? static::$PROPERTIES[$key]['default'] : null;

        switch ($aggregation) {
            case Enum\AggregationType::COLLECTION:
                $this->_values[$key] = array();
                if (is_array($value)) {
                    $aggregate_class = isset(static::$PROPERTIES[$key]['aggregate_class'])
                        ? static::$PROPERTIES[$key]['aggregate_class']
                        : self::$DEFAULT_PROPERTY['aggregate_class'];

                    $this->_values[$key] = new $aggregate_class($value, $type, $nullable, $default);
                }
                break;
            // TODO - Support Ranges (min, max, etc)
//            case Enum\AggregationType::RANGE:
//                $this->_values[$key] = new Range($value);
//                break;
            case Enum\AggregationType::SINGLE:
            default:
                $this->_values[$key] = self::castValue($value, $type, $nullable, $default);
                break;
        }
        $this->_initialized[$key] = true;
    }

    /**
     * Casts the given property value to the type specified
     * @param $value
     * @param $type
     * @param bool $nullable
     * @param null $default
     * @return bool|Type\NullDateTime|float|int|null|string
     */
    public static function castValue($value, $type, $nullable=true, $default=null)
    {
        // If the value is nullable, return NULL for scalar types.
        // Objects will return an instance with a null constructor
        // Dates will return a NullDateTime
        if ($nullable && is_null($value)) {
            if (!is_null($default)) {
                // If a default exists, cast it and return it
                return self::castValue($default, $type, $nullable);
            } else {
                // Else return a null equivalent of nullable types
                switch ($type) {
                    case DataType::STRING:
                    case DataType::INTEGER:
                    case DataType::DECIMAL:
                    case DataType::BOOLEAN:
                        return null;
                        break;
                    case DataType::DATETIME:
                    case DataType::DATE:
                    case DataType::TIME:
                        return new Type\NullDateTime();
                        break;
                    default:
                        // Non-nullable types will continue on to cast "null" as their value
                }

            }
        }

        // In cases where the value is not null or not nullable, return the cast value
        switch ($type) {
            // Scalar datatypes
            case DataType::STRING:
                $value = (string) $value;
                break;
            case DataType::INTEGER:
                $value = (int) $value;
                break;
            case DataType::DECIMAL:
                $value = (double) $value;
                break;
            case DataType::BOOLEAN:
                $value = (bool) $value;
                break;
            // DateTime casting
            case DataType::DATETIME:
            case DataType::DATE:
            case DataType::TIME:

                $class = null;
                switch ($type) {
                    // DateTime class definition
                    // Set class and continue to the casting in the next step
                    case DataType::DATETIME:
                        $class = "\Qi\Archetype\Type\DateTime";
                        break;
                    case DataType::DATE:
                        $class = "\Qi\Archetype\Type\Date";
                        break;
                    case DataType::TIME:
                        $class = "\Qi\Archetype\Type\Time";
                        break;
                }

                if (is_a($value, $class)) {
                    // Do nothing to properly instantiated objects
                } else if ($value instanceof \DateTime) {
                    // For \DateTimes, create an instance of our class with the same timestamp as the datetime
                    $dt = new $class();
                    $dt->setTimestamp($value->getTimestamp());
                    $value = $dt;
                } else if (is_int($value) || !$value) {
                    // For other supported types, create a new $class
                    $dt = new $class();
                    $dt->setTimestamp((int) $value);
                    $value = $dt;
                } else {
                    $value = new $class($value);
                }
                break;

            default:
                // If "type" is a defined class, and value isn't already one of them
                if (class_exists($type) && !is_a($value, $type)) {
                    // Instantiate that class with value as the constructor param
                    $value = new $type($value);
                }
                // If not, leave value as is
                break;
        }

        return $value;
    }

    /**
     * @param Entity $entity
     * @return bool
     * @deprecated
     */
    public function equals(Entity $entity)
    {
        return $this->__toString() === $entity->__toString()
            && implode(',',$this->asArray()) == implode(',',$entity->asArray());
    }

    public function getRawValue($key)
    {
        return $this->_values[$key];
    }

    public function asRawArray()
    {
        return $this->_values;
    }

    /**
     * Returns the Archetype's data as an associative array\
     * @param bool $force_recursive_initialization - NOTE: This is an expensive operation.  Only do this for data submitted from a form
     * @return array
     */
    public function asArray($force_recursive_initialization=true)
    {
        // Build output values as an array
        // If a child is an Archetype Entity, retrieve its values as an associative array.
        $values = array();
        foreach (static::$PROPERTIES as $key => $properties) {
            if (isset($this->_values[$key])) {
                // If the key has not been initialized, do so now
                if ($force_recursive_initialization && !$this->_initialized[$key]) {
                    $this->_initialize($key);
                }

                if ($this->_values[$key] instanceof Entity
                        || $this->_values[$key] instanceof Iterator) {
                    // Entities and Iterators should be retrieved as arrays
                    $values[$key] = $this->_values[$key]->asArray($force_recursive_initialization);
                } else if (is_array($this->_values[$key])
                        && $properties['aggregation'] == AggregationType::COLLECTION) {
                    $values[$key] = array();
                    foreach($this->_values[$key] as $item) {
                        if ($item instanceof Entity) {
                            $values[$key][] = $item->asArray($force_recursive_initialization);
                        } else {
                            $values[$key] = $item;
                        }
                    }

                } else if ($this->_values[$key] instanceof Enum) {
                    // Archetype Enums should be reduced to scalar values
                    $values[$key] = $this->_values[$key]->getValue();

                } else if ($this->values[$key] instanceof Type\Date
                        || $this->values[$key] instanceof Type\Time
                        || $this->values[$key] instanceof Type\DateTime
                        || $this->values[$key] instanceof Type\NullDateTime) {
                    // Archetype Date/Time objects - retrieve the mysql-formatted string
                    $values[$key] = $this->_values[$key]->mysqlFormat();

                } else if ($this->_values[$key] instanceof \DateTime) {
                    // DateTime objects should be retrieved as MySQL-format datetime strings
                    $values[$key] = $this->_values[$key]->format("Y-m-d H:i:s");

                } else {
                    // Scalar values can be whatever
                    $values[$key] = $this->_values[$key];
                }
            } else {
                $values[$key] = null;
            }
        }
        return $values;
    }
    
    /**
     * Returns the Archetype's data as JSON
     * @return string
     */
    public function asJson()
    {
        $values = $this->asArray();
        return json_encode($values);
    }

    /**
     * Returns the Archetype's data as a stdClass object
     * @return \stdClass
     */
    public function asObject()
    {
        $values = $this->asArray();
        return (object) $values;
    }
    
    // ArrayAccess Implementation
    public function offsetExists($offset)
    {
        return isset($this->_values[$offset]);
    }
    
    public function offsetGet($offset)
    {
        return Entity::castValue($this->_values[$offset], $this->_type, $this->_nullable, $this->_default);
    }
    
    public function offsetSet($offset, $value)
    {
        $this->_values[$offset] = $value;
    }
    
    public function offsetUnset($offset)
    {
        unset($this->_values[$offset]);
    }
}
