<?php
namespace Qi\Form;

use Enumb\ListType;

/**
 * The RadioList class represents a radio button collection
 */
class RadioList extends ListElement
{
    /**
     * Constructor
     * @param $name String
     * @param $properties Array[optional]
     */
    public function __construct($name, array $properties=null) {
        parent::__construct(ListType::RADIO, $name, $properties);
    }
    
    /**
     * 
     * @return DOMNode the DOM Element
     * @param $dom DOMDocument
     */
    protected function getOption($key, $value, array $properties = null, $i) {
        // Placeholder for option generator functinoality
        $properties["label"] = $value;
        $properties["value"] = $key;
        $properties["required-symbol"] = false;
        return new RadioInput($properties["name"], $properties);
    }
}
