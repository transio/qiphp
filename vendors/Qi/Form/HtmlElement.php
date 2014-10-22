<?php
namespace Qi\Form;

/**
 * Hyperlink Node
 */
class HtmlElement extends Model\Element
{
    protected $html;
    
    /**
     * Constructor is protected, meaning Input cannot be instantiated
     * @param $inputType Enum\InputType
     * @param $name Object
     * @param $value Object
     * @param $title Object[optional]
     * @param $required Object[optional]
     * @param $disabled Object[optional]
     */
    public function __construct($html, $name=null)
    {
        $this->html = $html;
        $this->name = $name;
    }
    
    /**
     * Override Element->getNode
     * @return html
     */
    public function getNode()
    {
        return $this->html;
    }
}
