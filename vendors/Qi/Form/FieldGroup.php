<?php
namespace Qi\Form;

/**
 * The FieldGroup class represents a group (div) of related form fields, 
 */
class FieldGroup extends Model\Container
{    
    /**
     * Constructor
     * @param $legend String[optional] The name (legend) of the fieldset
     */
    public function __construct($name, array $properties=array())
    {
        parent::__construct("div", $name, $properties);
        $this->addClass("qf-fieldgroup");
    }
    
}
