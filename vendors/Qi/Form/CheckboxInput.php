<?php
namespace Qi\Form;

/**
 * The CheckboxInput class represents a single checkbox form element
 */
class CheckboxInput extends Input
{
    /**
     * Constructor
     * @param $name String
     * @param $prompt String
     * @param $properties Array[optional]
     */
    public function __construct($name, array $properties=null)
    {
        parent::__construct(InputType::CHECKBOX, $name, $properties);
        if (is_null($this->value)) {
            $this->value = true;
        }
    }
    
    public function setValue($value)
    {
        $values = is_array($value) ? $value : array($value);
        foreach($values as $value){
            if($this->value == $value) {
                   $this->checked = true;
                $break;
            }
        }
    }
    
    public function getData($data)
    {
        $value = parent::getData($data);
        return ($value) ? 1 : 0;
    }
}
