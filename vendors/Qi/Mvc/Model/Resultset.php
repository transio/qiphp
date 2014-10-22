<?php
namespace Qi\Archetype;

/**
 * @author Steven Moseley
 */
class Resultset extends Iterator
{
    private $_result;

    public function __construct(mysqli_result $result, $type, $nullable=true, $default=null)
    {
        $this->_result = $result;
        parent::__construct($type, $nullable, $default);
    }

    protected function _getCurrentRecord()
    {
        return $this->_result->fetch_assoc();
    }

    protected function _getRecordByKey($key)
    {
        // TODO - implement this to enable seek functionality
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->_result->field_count;
    }
}
