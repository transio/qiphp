<?php
namespace Qi\Form;

/**
 * Hyperlink Node
 */
class FbmlElement extends Element
{
    protected $fbml;
    
    /**
     * Constructor is protected, meaning Input cannot be instantiated
     * @param $inputType Enum\InputType
     * @param $name Object
     * @param $value Object
     * @param $title Object[optional]
     * @param $required Object[optional]
     * @param $disabled Object[optional]
     */
    public function __construct($fbml, $name=null)
    {
        $this->fbml = "<div xmlns:fb=\"http://www.facebook.com/2008/fbml\">{$fbml}</div>";
    }
    
    /**
     * Override Element->getNode
     * @return FBML string
     */
    public function &getNode()
    {
        this->fbml;
    }
}
