<?php
namespace Qi\Form;

/**
 * The DateInput class represents a date input form element
 */
class DateInput extends Input
{    
    /**
     * Constructor
     * @param $name String
     * @param $title String
     * @param $properties Array[optional]
     */
    public function __construct($name, array $properties=null)
    {
        if (is_null($properties)) $properties = array();
        parent::__construct(InputType::TEXT, $name, $properties);
        $this->addClass("qf-date");
    }
}
