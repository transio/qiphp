<?php
namespace Qi\Form;

/**
 * The PasswordInput class represents a password input form element
 */
class PasswordInput extends Input
{
    /**
     * Constructor
     * @param $name String
     * @param $title String
     * @param $properties Array[optional]
     */
    public function __construct($name, array $properties=null)
    {
        parent::__construct(\Qi\Form\Enum\InputType::PASSWORD, $name, $properties);
    }
    
    public function setData($data)
    {
        // Do not set data
        parent::setData("");
    }
    
    public function setValue($value)
    {
        // Do not set value
        parent::setValue("");
    }
}
