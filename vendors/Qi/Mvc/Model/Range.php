<?php
namespace Qi\Archetype;

/**
 * Dynamic Range Class
 * This is to be used to create a min-max range
 * @author: smoseley
 */
abstract class Range
{
    private $_data;
    private $_count;

    public function __construct(array $data)
    {
        $this->_data = $data;
        $this->_count = count($data);

        if (!$this->_count > 1) {
            usort($data, function($a, $b) {
                if (!$a instanceof ComparableEntity
                        || !$b instanceof ComparableEntity) {
                    throw new Exception('All items in a Range must be ComparableEntity.');
                }
                return $a->greaterThan($b);
            });
        }
    }

    public function count()
    {
        return $this->_count;
    }

    public function min()
    {
        return $this->_count ? $this->_data[0] : null;
    }

    public function max()
    {
        return $this->_count ? $this->_data[$this->_count - 1] : null;
    }

//    // TODO -  Implement other aggregate range functionality
//    public function avg() {}
//    public function med() {}
//    public function total() {}

}
