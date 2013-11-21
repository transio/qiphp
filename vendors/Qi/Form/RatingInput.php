<?php
namespace Qi\Form;

use Enum\InputType;

/**
 * The DateInput class represents a date input form element
 */
class RatingInput extends ListElement
{    
    /**
     * Constructor
     * @param $name String
     * @param $title String
     * @param $properties Array[optional]
     */
    public function __construct($name, array $properties=null)
    {
        if (is_numeric($properties["stars"])) {
            $options = array();
            for ($i = 1; $i <= $properties["stars"]; $i++) {
                $options[$i] = $i;
            }
            $properties["options"] = $options;
        }
        parent::__construct(ListType::RATING, $name, $properties);
    }
    
    /**
     * 
     * @return DOMNode the DOM Element
     * @param $dom DOMDocument
     */
    protected function getOption($key, $value, array $properties = null)
    {
        // Placeholder for option generator functinoality
        $properties["label"] = null;
        $properties["value"] = $key;
        $properties["noid"] = true;
        $option = new RadioInput($properties["name"], $properties);
        $option->addClass("qf-rating");
    }
}
