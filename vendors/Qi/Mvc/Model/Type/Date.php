<?php
namespace Qi\Archetype\Type;

/**
 * CLASS DESCRIPTION
 * @author smoseley
 */
class Date extends \DateTime
{
    public function __construct($datestring=null) {
        parent::__construct($datestring);
        $this->setTime(0,0,0);
    }
    public function __toString() {
        return $this->mysqlFormat();
    }
    public function mysqlFormat() {
        return $this->format('Y-m-d');
    }
    public function setTimestamp($timestamp) {
        $this->setTimestamp($timestamp);
        $this->setTime(0,0,0);
    }
}
