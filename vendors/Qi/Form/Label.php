<?php
namespace Qi\Form;

/**
 * @deprecated - Label Node
 */
class Label extends Element
{
    private $for;
    
    public function __construct($for, $title=null)
    {
        parent::__construct("label", "{$for}_label");
        $this->for = $for;
        $this->title = $title;
    }
    
    public function &getNode(DOMDocument &$dom=null)
    {
        $node = parent::getNode($dom);
           $node->setAttribute("for", $this->for);
        $node->nodeValue = $this->title;
        $spanNode = $dom->createElement("span");
        $spanNode->setAttribute("class", "qf-label-span");
        $spanNode->appendChild($node);
        return $spanNode;
    }
}
