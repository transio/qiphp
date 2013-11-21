<?php
namespace Qi\Archetype\Type;

/**
 * Archetype DateTime type - extends base DateTime
 * @author smoseley
 */
class DateTime extends \DateTime
{
    public function __toString() {
        return $this->mysqlFormat();
    }
    public function mysqlFormat() {
        return $this->format('Y-m-d H:i:s');
    }
}
