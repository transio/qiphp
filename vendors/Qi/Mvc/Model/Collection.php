<?php
namespace Qi\Archetype;

/**
 * @author Steven Moseley
 */
class Collection extends Iterator implements \ArrayAccess
{
    private $_data;
    private $_count;

    public function __construct(array $data, $type, $nullable=true, $default=null)
    {
        $this->_data = $data;
        parent::__construct($type, $nullable, $default);
    }

    protected function _getCurrentRecord()
    {
        return !empty($this->_data) && array_key_exists($this->_key, $this->_data) ? $this->_data[$this->_key] : null;
    }

    protected function _getRecordByKey($key)
    {
        return !empty($this->_data) && array_key_exists($this->_key, $this->_data) ? $this->_data[$key] : null;
    }

    /**
     * Push an element onto the end of a Collection
     * @param $element
     */
    public function push($element)
    {
        array_push($this->data, $element);
    }

    /**
     * Pop an element off the end of the Collection
     * @param $element
     * @return bool|Type\NullDateTime|float|int|null|string
     */
    public function pop($element)
    {
        return Entity::castValue(array_pop($this->data), $this->_type, $this->_nullable, $this->_default);
    }

    /**
     * @return int
     */
    public function count()
    {
        if (!$this->_count) $this->_count = count($this->_data);
        return $this->_count;
    }

    public function sort($sort_function) {
        if (is_callable($sort_function))
            usort($this->_data, $sort_function);
    }


    // ArrayAccess Implementation
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }
    
    public function offsetGet($offset)
    {
        return Entity::castValue($this->_data[$offset], $this->_type, $this->_nullable, $this->_default);
    }
    
    public function offsetSet($offset, $value)
    {
        $this->_data[$offset] = $value;
    }
    
    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }
}
