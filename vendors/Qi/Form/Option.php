<?php
namespace Qi\Form;

/**
 * The Option class represents a select input option element
 */
class Option extends Model\Element
{
    /**
     * Constructor
     * @param $value[optinoal] String
     * @param $title[optional] String
     * @param $properties Array[optional]
     */
    public function __construct($value="", $prompt="", $selectedValue=null)
    {
        parent::__construct("option", null, array("auto-id" => false, "value" => $value));
        
        // Extend the Element in a custom fashion
        $this->prompt = $prompt;
        $this->content = $prompt;
        $this->selected = false;
        if (!is_null($selectedValue)) $this->setValue($selectedValue);
    }
    
    public function setValue($value)
    {
        $this->selected = $this->value == $value || (is_array($value) && in_array($this->value, $value));
    }
}
