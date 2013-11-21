<?php
namespace Qi\Form;

use Enum\ButtonType;

/**
 * The Button class represents a button form element
 */
class Button extends Element
{
    
    /**
     * Constructor
     * @param $buttonType Object
     * @param $name String
     * @param $value String
     * @param $properties Array[optional]
     */
    //public function __construct($buttonType, $name, $value, array $properties=null) {
    public function __construct($name, $properties=null)
    {
        if (is_array($properties)) {
            // New functionality
            if (!isset($properties["type"])) $properties["type"] = ButtonType::SUBMIT;
            if (!isset($properties["value"])) $properties["value"] = "Submit";
        } else {
            // DEPRECATED
            // Backwards compatibility with old interface for existing apps
            // This will be removed in 1.1
            $DEPRECATED_name = $properties;
            if (is_array($DEPRECATED_properties)) {
                $properties = $DEPRECATED_properties;
            } else {
                $properties = array();
            }
            $properties["type"] = $name;
            $properties["value"] = $DEPRECATED_value;
            $name = $DEPRECATED_name;
        }
    
        parent::__construct("button", $name, $properties);
    }
    
    /**
     * Override Container->getNode
     * @return DOMNode the DOM Element
     * @param $dom DOMDocument
     */
    public function &getNode(DOMDocument &$dom=null)
    {
        if (!is_null($dom)) $this->dom = $dom;
        
        // Get the button node from Container
            $node = parent::getNode($dom);

                // Create a div wrapper node
                $wrapperNode = $this->dom->createElement("div");
                $wrapperNode->setAttribute("class", "qf-button-wrapper");
                $wrapperNode->setAttribute("id", $this->getId() . self::PREFIX_SEPARATOR . "wrapper");

                // Create a span node
                $spanNode = $this->dom->createElement("span");


            
        // Append the button text to the span
            $spanNode->nodeValue = $this->value;
        //print_r($this);
        
        // Append the span to the button
        $node->appendChild($spanNode);
        
        // Append button to wrapper node
        $wrapperNode->appendChild($node);
        
        // Return wrapper node
        return $wrapperNode;
    }
    
    public function setData($data)
    {
    }
    
    public function setValue($value)
    {
        if (strlen($value)) {
            parent::setValue($value);
        }
    }
    
}
