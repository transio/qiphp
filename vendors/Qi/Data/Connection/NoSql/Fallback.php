<?php
namespace Qi\Data\Source\NoSql;

class Fallback
    implements Connection
{
    const MODE_READ_ONLY = 'read_only';
    const MODE_WRITE_MISSES_TO_PARENT = 'write_misses';

    private $_primary;
    private $_fallback;
    private $_mode;

    public function __construct(Connection $primary, Connection $fallback, $mode = self::MODE_READ_ONLY)
    {
        $this->_primary = $primary;
        $this->_fallback = $fallback;
        $this->_mode = $mode;
    }

    public function get($key)
    {
        $value = $this->_primary->get($key);
        if (!$value) {
            $value = $this->_fallback->get($key);

            // If write-to-parent mode enabled, write the missed value to the parent
            if ($value && $this->_mode == self::MODE_WRITE_MISSES_TO_PARENT) {
                $this->_primary->set($key, $value);
            }
        }
        return $value;
    }

    public function getMany(array $keys)
    {
        $values = $this->_primary->getMany($keys);
        if (count($values) != count($keys)) {
            // Find the missed keys
            $misses = array();
            foreach ($keys as $key) {
                if (!isset($values[$key]) || !$values[$key]) $misses[] = $key;
            }

            if ($this->_fallback) {
                // If a fallback exists, try to get the missed keys from it
                $fallback_values = $this->_fallback->getMany($misses);
                if (!empty($fallback_values)) {
                    // Merge in missing values from the fallback
                    foreach ($fallback_values as $key => $value) {
                        $values[$key] = $value;
                    }

                    // If write-to-parent mode enabled, write the missed values to the parent
                    if ($this->_mode == self::MODE_WRITE_MISSES_TO_PARENT) {
                        $this->_primary->setMany($fallback_values);
                    }
                }
            }
        }
        return $values;
    }

    public function set($key, $value)
    {
        $this->_primary->set($key, $value);
        if ($this->_fallback) $this->_fallback->set($key, $value);
    }

    public function setMany(array $values)
    {
        $this->_primary->setMany($values);
        if ($this->_fallback) $this->_fallback->setMany($values);
    }
}