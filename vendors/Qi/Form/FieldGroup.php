<?php
namespace Qi\Form;

/**
 * The FieldGroup class represents a group (div) of related form fields, 
 */
class FieldGroup extends Container
{    
    /**
     * Constructor
     * @param $legend String[optional] The name (legend) of the fieldset
     */
    public function __construct($name, array $properties=null)
    {
        parent::__construct("div", $name, $properties);
        $this->addClass("qf-fieldgroup");
    }
    
    /**
     * Override Container->getNode
     * @return \DOMNode The \DOM Element
     * @param $dom \DOMDocument
     */
    public function &getNode(\DOMDocument &$dom=null)
    {
        $node =& parent::getNode($dom);
        return $node;
    }
}
