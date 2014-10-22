<?php
namespace Qi\Archetype;

/**
 * CLASS DESCRIPTION
 * @author Steven Moseley
 */
abstract class ComparableEntity extends Entity
{
    /**
     * Returns the comparable value of the Entity
     */
    public function getValue()
    {
        return $this->_toString();
    }

    /**
     * Test to see if this Entity is greater in value than another Entity object
     */
    public function greaterThan(ComparableEntity $otherEntity)
    {
        return $this->_toString() > $otherEntity->_toString();;
    }

    /**
     * Test to see if this Entity is less in value than another Entity object
     */
    public function lessThan(ComparableEntity $otherEntity)
    {
        return $this->_toString() < $otherEntity->_toString();;
    }

    /**
     * Test to see if this Entity is equal in value to another Entity object
     */
    public function equals(ComparableEntity $otherEntity)
    {
        return $this->_toString() == $otherEntity->_toString();;
    }
}
