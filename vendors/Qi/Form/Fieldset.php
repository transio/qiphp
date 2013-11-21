<?php
namespace Qi\Form;

/**
 * The Fieldset class represents a fieldset object, 
 */
class Fieldset extends Container
{
    private $legend;
    
    /**
     * Constructor
     * @param $legend String[optional] The name (legend) of the fieldset
     */
    public function __construct($name, $properties=null)
    {    
        parent::__construct("fieldset", $name, $properties);
        if (is_array($properties)) {
            if (isset($properties["legend"])) {
                $this->legend = $properties["legend"];
            }
        }
    }
    
    /**
     * Override Container->getNode
     * @return DOMNode The DOM Element
     * @param $dom DOMDocument
     */
    public function &getNode(DOMDocument &$dom=null)
    {
        $node = parent::getNode($dom);
        if (!is_null($this->legend) && strlen($this->legend)) {
            $legendNode = $dom->createElement("legend", $this->legend);
            if ($node->hasChildNodes()) {
                $node->insertBefore($legendNode, $node->childNodes->item(0));
            } else {
                $node->appendChild($legendNode);
            }
        }
        return $node;
    }
}
