<?php
namespace Qi\Form;

/**
 * @deprecated - Label Node
 */
class Label extends Model\Element
{
    public function __construct($for, $title = "")
    {
        parent::__construct("label", "{$for}_label");
        $this->for = $for;
        $this->title = $title;
    }
    
    /**
     * Override Element->getNode
     */
    public function &getNode()
    {
        return parent::getNode(array("content" => $this->title));
    }

}
