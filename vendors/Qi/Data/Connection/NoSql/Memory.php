<?php
namespace Qi\Data\Source\NoSql;

/**
 * Memory class - stores key/value pairs in memory for retrieval
 */
class Memory
    implements Connection
{
    private $_values;

    public function get($key)
    {
        return isset($this->_values[$key]) ? $this->_values[$key] : null;
    }

    public function getMany(array $keys)
    {
        $misses = array();
        $values = array();

        // Find keys that are stored locally, and store the keys that were missed
        foreach ($keys as $key) {
            if (isset($this->_values[$key])) {
                $values[$key] = $this->_values[$key];
            }
        }
        return $values;
    }

    public function set($key, $value)
    {
        $this->_values[$key] = $value;
        return true;
    }

    public function setMany(array $key_value_pairs)
    {
        foreach ($key_value_pairs as $key => $value) {
            $this->_values[$key] = $value;
        }
        return true;
    }
}
