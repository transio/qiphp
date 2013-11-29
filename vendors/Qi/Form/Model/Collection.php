<?php
namespace Qi\Form\Model;

/**
 * An HTML collection / list element (e.g. select, radio list, check list)
 * A collection must contain items of the same type
 *
 */
class Collection extends Element {
    protected $childElements = array();
    protected $tabs = array();
    
    /**
     * Constructor is protected - cannot be instantiated
     * @param $elementName Object
     * @param $name Object
     * @param $properties Object[optional]
     */
    public function __construct($elementName, $name, array $properties=null) {
        parent::__construct($elementName, $name, $properties);
    }

    /**
     * Destructor
     */
    public function __destruct() {
        parent::__destruct();
        unset($this->childElements);
    }

    /**
     * Returns the fully populated \DOM Element with all children
     */
    public function &getNode(\DOMDocument &$dom=null) {
        // Get the node
        $node = parent::getNode($dom);
        
        // Add tabs, if applicable
        if (!empty($this->tabs)) {
            $node->appendChild($this->getTabs($this->dom));
        }

            
        // Add all child elements
    $i = 0;$c = count($this->childElements);
        foreach ($this->childElements as $element) {
        //if (
            
        if (get_class($element) == "AccordianGroup") {
            $GLOBALS["settings"]->html->loadScript .= "\$('" . $this->getId() . "').accordian();\n";
        }
            $el = $element->getNode($this->dom);
            if (!is_null($el)) {
                if (is_array($el)) {
                    foreach ($el as $e) {
                        $node->appendChild($e);
                    }
                } else{
                    $node->appendChild($el);
                }
            }
        }
        
        // Return it
        return $node;
    }
    
    /**
     * Add an element to the $elements collection
     */
    public function addElement(Element &$element, $pos=null) {
        // If the position isn't specified or is invalid
        if (is_null($pos) || !is_int($pos) || $pos > count($this->childElements)) {
            // Push new item onto end of array
            array_push($this->childElements, $element);
        } else {
            // Insert new item at specified pos
            $newArray = array();
            for ($i = 0; $i < count($this->childElements); $i++) {
                $j = $i < $pos ? $i : $i+1;
                if ($i == $pos) $newArray[$i] = $element;
                $newArray[$j] = $this->childElements[$i];
            }
        }
        
        if (get_class($element) == "TabGroup") {
            array_push($this->tabs, new TabItem($element->getId(), $element->title, $this->wizard));
        }
        return $this;
    }
    
    /**
     * Synonym of item()
     * @return 
     * @param $name Object
     */
    public function getChild($name) {
        return $this->item($name);
    }
    
    /**
     * Return an item by its "name" value
     * @return 
     * @param $name Object
     */
    public function item($name) {
        foreach ($this->childElements as $el) {
            if ($el->name == $name) {
                return $el;
            }
        }
    }
}
