<?php
namespace Qi\Form;

/**
 * The Option class represents a select input option element
 */
class Option extends Element
{
    /**
     * Constructor
     * @param $value[optinoal] String
     * @param $title[optional] String
     * @param $properties Array[optional]
     */
    public function __construct($value="", $prompt="", $values=null)
    {
        parent::__construct("option", null, array("auto-id" => false, "value" => $value));
        
        // Extend the Element in a custom fashion
        $this->prompt = $prompt;
           $this->selected = false;
        if (!is_null($values)) $this->setValue($values);
    }
    
    public function setValue($value)
    {
        $values = is_array($value) ? $value : array($value);
        foreach($values as $value){
            if($this->value == $value) {
                   $this->selected = true;
                $break;
            }
        }
    }
    
    /**
     * Override Element->getNode
     * Returns the \DOM Element
     */
    public function &getNode(\DOMDocument &$dom=null)
    {
        $node = parent::getNode($dom);
        if (!is_null($this->prompt)) $node->nodeValue = $this->prompt;
        return $node;
    }
}
