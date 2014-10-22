<?php
namespace Qi\Archetype;

use Qi\Archetype\Enum\DataFormat;

/**
 * @author Steven Moseley
 */
abstract class Iterator implements \Iterator
{
    protected $_type;
    protected $_nullable;
    protected $_default;
    protected $_current;
    protected $_key;

    public function __construct($type, $nullable, $default)
    {
        $this->_type = $type;
        $this->_nullable = $nullable;
        $this->_default = $default;
        $this->rewind();
    }

    // Custom Methods
    /**
     * Retrieve the current record.  This should return null at the end of the iterator
     * @return null
     */
    protected function _getCurrentRecord()
    {
        // To be implemented by child classes
        return null;
    }
    protected function _getRecordByKey()
    {
        // To be implemented by child classes
        return null;
    }

    /**
     * Count the records in an Iterable.  This extends the default functionality of an Iterator.
     * @return null
     */
    public function count()
    {
        // To be implemented by child classes
        return null;
    }


    // Iterator Methods
    /**
     * Get the first item of an Iterable.  This extends the default Iterator functionality
     * @return Archetype|null
     */
    public function first()
    {
        if ($this->valid()) {
            $this->rewind();
            return $this->current();
        }
    }

    /**
     * Overrides the Iterator rewind() method.
     * @return null
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * Overrides the Iterator valid() method.
     * Returns true if the current item is an array or is an instance of the defined type
     * @return Boolean
     */
    public function valid()
    {
        return is_array($this->_current) || is_a($this->_current, $this->_type);
    }

    /**
     * Overrides the Iterator current() method.  Loads the current record as a class
     * @return mixed|null
     * @throws Exception
     */
    public function current()
    {
        return Entity::castValue($this->_current, $this->_type, $this->_nullable, $this->_default);
    }

    /**
     * Overrides the Iterator key() method.
     * @return int The current Key
     */
    public function key()
    {
        return $this->_key;
    }

    /**
     * Overrides the Iterator next() method.
     * @return null
     */
    public function next()
    {
        $this->_key++;
        $this->_current =& $this->_getCurrentRecord();
    }

    /**
     * Overrides the Iterator seek() method.
     * @return null
     * @param $lineNumber Object
     */
    public function seek($row)
    {
        $this->_key = $row;
        $this->_current =& $this->_getRecordByKey($this->_key);;
    }

    /**
     * @param DataFormat $format
     * @return array
     */
    public function asArray()
    {
        $values = array();
        foreach ($this as $object) {
            $values[] = $object->asArray();
        }
        return $values;
    }

    public function asObject()
    {
        $values = $this->asArray();
        return (object) $values;
    }

    public function asJson()
    {
        $values = $this->asArray();
        return json_encode($values);
    }
}
