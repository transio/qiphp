<?php
namespace Qi\Form;

/**
 * The TabGroup class represents a jQuery UI tab group (div) of related form fields, 
 */
class TabGroup extends Container
{    
    /**
     * Constructor
     */
    public function __construct($name, $properties=null)
    {
        // "title" property is used to specify the tab title
    
        parent::__construct("div", $name, $properties);
        $this->addClass("qf-tabgroup");
    }
    
    /**
     * Override Container->getNode
     * @return \DOMNode The \DOM Element
     * @param $dom \DOMDocument
     */
    public function &getNode(\DOMDocument &$dom=null)
    {
        $node = parent::getNode($dom);
        return $node;
    }
}
