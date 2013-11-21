<?php
namespace Qi\Form;

/**
 * Hyperlink Node
 */
class HtmlElement extends Element
{
    protected $html;
    
    /**
     * Constructor is protected, meaning Input cannot be instantiated
     * @param $inputType InputType
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
     * @return DOMNode The DOMNode for this element
     * @param $dom DOMDocument
     */
    public function getNode(DOMDocument &$dom=null)
    {
        try {
            $htmlDom = new DOMDocument();
            if ($this->name) {
                $id = $this->getId();
                $html = "<div id=\"{$id}\">{$this->html}</div>";
            } else {
                $html = "<div>{$this->html}</div>";
            }
            $htmlDom->loadHTML($html);
            return $dom->importNode($htmlDom->documentElement->firstChild->firstChild, true);
        } catch (Exception $e) {
            print($e->getMessage());
        }
    }
}
