<?php
namespace Qi\Form;

/**
 * The Select class represents a select input form element
 */
class Select extends ListElement
{
    /**
     * Constructor
     * @param $name String
     * @param $title String
     * @param $properties Array[optional]
     * @param $options Array[optional]
     */
    public function __construct($name, array $properties=null)
    {
        if (!array_key_exists("prompt", $properties)) {
            /*
            if (array_key_exists("title", $properties)) {
                $properties["prompt"] = "<".$properties["title"].">";
            } else {
                $properties["prompt"] = "";
            }
            */
        }
        parent::__construct(ListType::SELECT, $name, $properties);
    }
    
    /**
     * 
     * @return DOMNode the DOM Element
     * @param $dom DOMDocument
     */
    protected function wrapNode(DOMDocument &$dom, DOMNode $node)
    {
        $innerNode = $dom->createElement("span");
        $innerNode->setAttribute("class", "qf-{$this->listType}-inner");
        $innerNode->setAttribute("id", $this->getId() . self::PREFIX_SEPARATOR . "inner");
        $innerNode->appendChild($node);
        return parent::wrapNode($dom, $innerNode);
    }
    
    /**
     * 
     * @return DOMNode the DOM Element
     * @param $dom DOMDocument
     */
    protected function getOption($key, $value, array $properties=null, $i=0)
    {
        return new Option($key, $value);
    }
}
