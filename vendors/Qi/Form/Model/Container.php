<?php
namespace Qi\Form\Model;

/**
 * A simple HTML block / container element (form, fieldset, etc.)
 * Containers may contain elements of different types
 */
class Container extends Collection {
    
    /**
     * Constructor
     * @param $elementName String The tagname of the element
     * @param $name String The name attribute for the element
     */
    public function __construct($elementName, $name, array $properties=null) {
        // Handle children property
        if (isset($properties["children"])) {
            $children = $properties["children"];
            $properties["children"] = null;
        }
        
        parent::__construct($elementName, $name, $properties);
        
        // Add children if specified
        if (isset($children) && is_array($children)) {
            foreach ($children as $child) {
                try {
                    $this->addElement($child);
                } catch (Exception $e) {
                    // TODO - Handle more gracefully?
                    throw $e;
                }
            }
        }
    }
    
    /**
     * Add an element to the $elements collection
     * @return Element The QiForm Element
     * @param $element Element A QiForm Element
     */
    public function addElement(Element &$element, $pos=null) {
        if ($this->name) $element->addPrefix($this->name);
        if ($this->prefix) $element->addPrefix($this->prefix);
        
        parent::addElement($element);
        
        // If the added element is another container,
        // Tell it to register new elements with the parent (this)
        if (array_key_exists("Container", class_parents($element))) {
            $element->addEventListener(\Qi\Form\Enum\ListenerType::ADD_ELEMENT, $this, "onChildContainerAddElement");
        }

        //Dispatch an event saying an element was added to this container
        $this->onChildContainerAddElement($element);

        // Return the element reference
        return $this;
    }
    
    /**
     * Override default "add prefix" to ensure adding
     * prefix to contianed elements
     */
    public function addPrefix($prefix=null) {
        parent::addPrefix($prefix);
        // Make sure to add the prefix to all contained elements, as well
        foreach ($this->childElements as $element) {
            $element->addPrefix($prefix);
        }
    }
    
    /**
     * When a child container dispatches an ADD_ELEMENT event
     * Displatch the event back up to any listening parent containers
     * @param $args Array event arguements
     */
    public function onChildContainerAddElement(&$args) {
        $this->dispatchEvent(\Qi\Form\Enum\ListenerType::ADD_ELEMENT, $args);
    }
    
    /**
     * 
     * @return \DOMElement
     * @param $dom Object
     */
    public function &getTabs(\DOMDocument &$dom) {
        $tabNode = $dom->createElement("ul");
        foreach ($this->tabs as $tab) {
            $tabNode->appendChild($tab->getNode($dom));
        }
        return $tabNode;
    }
    
    
    /**
     * Load data from the container into an array
     * @param $data Data The collection of data to load from
     * @return String The loaded value
     */
    public function getData($data) {
        if (is_array($data)) {
            $output = array();
            foreach ($this->childElements as $element) {
                $output[$element->name] = $element->getData($data);
            }
            return $output;
        }
    }

    /**
     * Load data into the container front an array
     * @param $data Array The collection of data to load from
     */
    public function setData($data) {
        if (is_array($data)) {
            foreach ($this->childElements as $element) {
                if (array_key_exists($element->name, $data)) {
                    $element->setData($data[$element->name]);
                }
            }
        }
        return $this;
    }
}
