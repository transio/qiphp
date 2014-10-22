<?php
namespace Qi\Form;

/**
 * The TextInput class represents a text input form element
 */
class TextInput extends Input
{
    /**
     * Constructor
     * @param $name String
     * @param $title String
     * @param $properties Array[optional]
     */
    public function __construct($name, array $properties=array())
    {
        parent::__construct(\Qi\Form\Enum\InputType::TEXT, $name, $properties);
    }
}
