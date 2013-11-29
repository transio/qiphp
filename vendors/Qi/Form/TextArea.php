<?php
namespace Qi\Form;

/**
 * The TextArea class represents a textarea form element
 */
class TextArea extends Element
{
    /**
     * Constructor
     * @param $name String
     * @param $properties Array[optional]
     */
    public function __construct($name, array $properties=null)
    {
        parent::__construct("textarea", $name, $properties);
        if (strlen($this->value) == 0) $this->value = "\r";
    }
    
    /**
     * Override Element->getNode
     * @return \DOMNode the \DOM Element
     * @param $dom \DOMDocument
     */
    public function &getNode(\DOMDocument &$dom=null)
    {
        $divNode = $dom->createElement("div");
        $divNode->setAttribute("class", "qf-textarea-wrapper" . ($this->markup ? " qf-markup-{$this->markup}-wrapper" : ""));
        $divNode->setAttribute("id", $this->getId() . self::PREFIX_SEPARATOR . "wrapper");
        $node = parent::getNode($dom);
        $node->nodeValue = $this->value;
        if (!is_null($this->label)) {
            $label = $this->generateLabel($dom);
            $divNode->appendChild($label);
        }
        $divNode->appendChild($node);
        return $divNode;
    }
}
