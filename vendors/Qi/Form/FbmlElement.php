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
     * @param $inputType InputType
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
     * @return DOMNode The DOMNode for this element
     * @param $dom DOMDocument
     */
    public function &getNode(DOMDocument &$dom=null)
    {
        try {
            $fbmlDom = new DOMDocument();
            $fbmlDom->loadXml($this->fbml);
            return $dom->importNode($fbmlDom->documentElement, true);
        } catch (Exception $e) {
            print($e->getMessage());
        }
    }
}
