<?php
namespace Qi\Form;

/**
 * The TabGroup class represents a jQuery UI tab group (div) of related form fields, 
 */
class TabGroup extends Model\Container
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
    
    public function &getNode(array $properties = array())
    {
        // Add tabs, if applicable
        $properties["content"] = $this->getTabs();
        return parent::getNode($properties);
    }
}
