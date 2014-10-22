<?php
namespace Qi\Form;

/**
 * The Button class represents a button form element
 */
class Button extends Model\Element
{
    /**
     * Constructor
     * @param $name String
     * @param $properties Array[optional]
     */
    public function __construct($name, array $properties=array())
    {
        if (!isset($properties["type"])) $properties["type"] = Enum\ButtonType::SUBMIT;
        if (!isset($properties["value"])) $properties["value"] = "Submit";
        parent::__construct("button", $name, $properties);
    }
}
