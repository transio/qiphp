<?php
namespace Qi\Form;

/**
 * The RadioInput class represents a radio input form element
 */
class RadioInput extends Input
{
    /**
     * Constructor
     * @param $name String
     * @param $title String
     * @param $properties Array[optional]
     */
    public function __construct($name, array $properties=null)
    {
        parent::__construct(\Qi\Form\Enum\InputType::RADIO, $name, $properties);
    }
    
    public function setValue($value)
    {
        if($this->value == $value) {
               $this->checked = true;
            $break;
        }
    }
}
