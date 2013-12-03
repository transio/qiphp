<?php
namespace Qi\Form;

/**
 * The TextArea class represents a textarea form element
 */
class TextArea extends Element
{
    /**
     * Constructor
     * @param $name String
     * @param $properties Array[optional]
     */
    public function __construct($name, array $properties=null)
    {
        parent::__construct("textarea", $name, $properties);
        if (strlen($this->value) == 0) $this->value = "\r";
    }
    
    /**
     * Override Element->getNode
     * @return \DOMNode the \DOM Element
     * @param $dom \DOMDocument
     */
    public function &getNode()
    {
        return parent::getNode(array("content" => $this->value));
    }
}
