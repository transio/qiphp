<?php
namespace Qi\Form;

/**
 * The CheckboxList class represents a multiple checkbox form element
 */
class CheckboxList extends ListElement
{    
    /**
     * Constructor
     * @param $name String
     * @param $title String
     * @param $properties Array[optional]
     */
    public function __construct($name, array $properties=null)
    {
        parent::__construct(Enum\ListType::CHECKBOX, $name, $properties);
    }
    
    /**
     * 
     * @return \DOMNode the \DOM Element
     * @param $dom \DOMDocument
     */
    protected function getOption($key, $value, array $properties = null, $i=0)
    {
        // Placeholder for option generator functinoality
        $properties["label"] = $value;
        $properties["value"] = $key;
        $properties["required-symbol"] = false;
        return new CheckboxInput($properties["name"], $properties);
    }
}
