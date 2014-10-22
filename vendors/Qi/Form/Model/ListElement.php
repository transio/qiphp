<?php
namespace Qi\Form\Model;

use \Qi\Form\Enum\ListType;

/**
 * The Select class represents a select input form element
 */
class ListElement extends Collection
{
    protected $listType;
    
    /**
     * Constructor
     * @param $name String
     * @param $title String
     * @param $properties Array[optional]
     * @param $options Array[optional]
     */
    public function __construct($listType, $name, array $properties=null)
    {
        $this->listType = $listType;
        
        switch ($listType) {
            case ListType::SELECT:
                $elementName = "select";
                break;
            case ListType::RADIO:
            case ListType::CHECKBOX:
                $elementName = "div";
                break;
            default:
                return;
        }
        parent::__construct($elementName, $name, $properties);
        
        // Create the option prompt if exists
        if ($listType == ListType::SELECT && isset($properties["prompt"])) {
            $option = new Option("", $properties["prompt"]);
            $this->addElement($option);
        }
        
        // Set options
        if (isset($properties["options"]) && is_array($properties["options"])) {
            $this->setOptions($properties["options"]);
        }
        
        if (array_key_exists("value", $properties)) {
            $this->setData($properties["value"]);
        }
    }
    
    /**
     * Set values from an array
     * @param $options array An array of options
     * @param var A variant index of the option value key in the array (defaults to 0)
     * @param var A variant index of the option text key in the array (defaults 1)
     *
     */
    public function setOptions(array $options)
    {
        // Create the option list
        if (is_array($options) || is_object($options)) {
            $i = 0;
            foreach ($options as $key => $value) {
                $i++;
                if (is_array($value)) {
                    if (count($value) >= 2)  {
                        $key = $value[0];
                        $value = $value[1];
                    } else {
                        $key = $value[0];
                        $value = $value[0];
                    }
                }
                $this->addOption($key, $value, $i);
            }
        }
        return $this;
    }
    
    public function addOption($key, $value, $i=0, array $extProperties = null)
    {
        $name = $this->getName();
        // Set up the name and id properties of the option
        $properties = array("name" => ($this->listType == ListType::CHECKBOX ? $name . "[]" : $name),
                            "id" => $name . self::PREFIX_SEPARATOR . $i,
                            "noprefix" => $this->noprefix ? true : false,
                            "required" => $this->required,
                            "title" => $this->title);
        if (!empty($extProperties)) {
            $properties = array_merge($properties, $extProperties);
        }
                            
        // Create the option node and apppend it to this node
        $option = $this->getOption($key, $value, $properties, $i);
        $this->addElement($option);
        return $this;
    }
    
    
    /**
     * Override default "add prefix" to ensure adding
     * prefix to contianed elements
     */
    public function addPrefix($prefix=null)
    {
        parent::addPrefix($prefix);
        // Make sure to add the prefix to all contained elements, as well
        foreach ($this->_children as $element) {
            $element->addPrefix($prefix);
        }
        return $this;
    }
    
    /**
     * Override Element->getNode
     * @return \DOMNode the \DOM Element
     * @param $dom \DOMDocument
     */
    public function getNode()
    {
        $node = parent::getNode();
        return $this->wrapNode($this->dom, $node);
    }
    
    /**
     * 
     * @return \DOMNode the \DOM Element
     * @param $node
     */
    protected function wrapNode($node)
    {
        $id = $this->getId() . self::PREFIX_SEPARATOR . "wrapper";
        $class = "qf-{$this->listType}-wrapper";

        if (!is_null($this->label) && strlen($this->label)) {
            $label = $this->generateLabel();
        }
        
        // Placeholder for wrapper generator functinoality
        $html = <<<HTML
            <div id="{$id}" class="{$class}">
                {$label}
                {$node}
            </div>
HTML;
        return $html;
    }
    
    /**
     * Placeholder for option generator functinoality
     * @return \DOMNode the \DOM Element
     * @param $dom \DOMDocument
     */
    protected function getOption($key, $value, array $properties = null, $i=0)
    {
        return new Option($key, $value);
    }
    
    
    /**
     * Override Element::getData
     * @return Variant
     * @param $data array
     */
    public function getData($data)
    {
        $name = $this->getName();
        if (is_array($data) && isset($data[$name])) {
            $value = $data[$name];
        } else if (is_object($data) && isset($data->$name)) {
            $value = $data->$name;
        }
        if (isset($value) && (!empty($value) || strlen($value) > 0)) {
            $this->value = $value;
        }
        return isset($value) ? $value : null;
    }
    
    /**
     * Load data into the element from an array or an object
     * @param $data Array The collection of data to load from
     */
    public function setData($data)
    {
        parent::setData($data);
        
        // Set selected options array from supplied value
        foreach($this->_children as $option) {
            $option->setValue($data);
        }
        return $this;
    }
}
