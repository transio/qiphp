<?php
namespace Qi\Form;

/**
 * The Fieldset class represents a fieldset object, 
 */
class Fieldset extends Container
{
    private $legend;
    
    /**
     * Constructor
     * @param $legend String[optional] The name (legend) of the fieldset
     */
    public function __construct($name, array $properties=array())
    {    
        parent::__construct("fieldset", $name, $properties);
        if (is_array($properties)) {
            if (isset($properties["legend"])) {
                if (empty($properties["children"])) $properties["children"] = array();
                array_unshift($properties["children"], new HtmlElement("<legend>{$properties['legend']}</legend>"));
            }
        }
    }
}
