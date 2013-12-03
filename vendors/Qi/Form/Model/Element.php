<?php
namespace Qi\Form\Model;

/**
 * A \DOM HTML element wrapper
 *
 */
class Element {
    // Separator for Element Name and ID prefixes
    const PREFIX_SEPARATOR = "__";
    
    // Element name (for form submission)
    protected $elementName;
    
    // Properties collection
    protected $properties = array();
    
    // HTML Attributes collection
    protected $attributes = array();
    
    // Collection of element prefixes
    protected $prefixes = array();
    
    // Classes collection (used to store "qf-" classes for javascript functionality)
    protected $extraClasses = array();
    
    // Back-end Event listeners with callback handlers
    protected $eventListeners = array();
    
    // Browser Event handlers
    protected $events = array();
    
    protected $content = "";

    /**
     * Constructor - initializes the Element
     * @return 
     * @param $elementName Object
     * @param $name Object[optional]
     * @param $properties Array[optional] Sets properties for rendering of the Element.
     * Form Attributes:
     *      "action" - String (null) - If set, will set the "action" attribute for the Form.
     *      "method" - String (null) - If set, will set the "method" attribute for the Form.
     *      "enctype" - String (null) - If set, will set the "enctype" attribute for the Form.
     *      "validation" - String (null) - If set, will set the validation type for the Form.
     * Attributes:
     *      "prefix" - String($name) - If set, will set the id and name prefix for this Element or all contained Elements.
     *      "noprefix" - Boolean (false) - If set to true, will force a no-prefix element name
     *      "id" - String (null) - If set, will force an id for the Element.
     *      "noid" - Boolean (false) - If set to true, will force element not to render an id
     *      "type" - String (null) - If set, will set the "type" attribute for the Element.
     *      "class" - String ($name) - If set, will set the "class" attribute for the Element.
     *      "style" - String ($value) - If set, will set the "style" attribute for the Element.
     *      "name" - String ($name) - If set, will set the "name" attribute for the Element.
     *      "title" - String ($name) - If set, will set the "title" attribute for the Element. Default to "label" if not supplied
     *      "value" - Variant (null) - If set, will set the "value" of the Element.  String, or Array for multi-
     *      "size" - int (null) - If set, will set the "size" attribute of the Element.
     *      "rows" - int (null) - If set, will set the "rows" attribute of the Element.
     *      "cols" - int (null) - If set, will set the "cols" attribute of the Element.
     *      "autocomplete" - Boolean (true) - If set to false, will set the "autocomplete" attribute of the Element.
     *      "disabled" - Boolean (false) - If set to true, will set the "disabled" attribute of the Element.
     *      "multiple" - Boolean (false) - If set to true, will set the "multiple" attribute of the Element.
     *      "selected" - Boolean (false) - If set to true, will set the "selected" attribute of the Element.
     *      "checked" - Boolean (false) - If set to true, will set the "checked" attribute of the Element.
     *      "readonly" - Boolean (false) - If set to true, will set the "readonly" attribute of the Element.
     *      "maxlength" - int (null) - If set, will set the required max length of the Element.
     *      "tags" - String (null) - If set, allow the specified HTML tags in the input. "*" implies all tags allowed.
     *      "for" - String (null) - 
     * Extended attributes:
     *      "auto-id" - Boolean (true) - If set to false, no id will generate.
     *      "required" - Boolean (false) - If set to true, the Element will be required.
     *      "required-symbol" - Boolean (= "required") - If set to false, no star will show when a field is required.
     *      "confirm" - String (null) - If set, will set this field as a confirmer of specified Element.
     *      "label" - String (null) - If set, will invoke creation of a corresponding label Element.
     *      "minlength" - int (null) - If set, will set the required min length of the Element.
     *      "format" - String (null) - If set, will set a required RegEx format for the Element.
     *      "minimum" - number (null) - If set, will set a required minimum numeric or age value for the Element.
     *      "mask" - String (null) - If set, will define a formatting mask for the field
     *      "prompt" - String (null) - If set, will invoke creation of a prompt for Select / Check / Radio
     *      "info" - String (null) - If set, will invoke creation of a corresponding pop-up info Element.
     *      "markup" - String (null) - If set, will set a MarkItUp! format.
     * Events: If set, will add the appropriate javascript event
     *      EventType::LOAD:
     *      EventType::UNLOAD:
     *      EventType::KEY_DOWN:
     *      EventType::KEY_UP:
     *      EventType::KEY_PRESS:
     *      EventType::MOUSE_OVER:
     *      EventType::MOUSE_OUT:
     *      EventType::MOUSE_DOWN:
     *      EventType::MOUSE_UP:
     *      EventType::CLICK:
     *      EventType::DOUBLE_CLICK:
     *      EventType::FOCUS:
     *      EventType::BLUR:
     *      EventType::PRESS:
     *      EventType::RELEASE:
     *      EventType::CHANGE:
     *      EventType::SUBMIT:
     *      EventType::RESET:
     */
    public function __construct($elementName, $name=null, array $properties=null) {
        // Default to no tags allowed
        $this->tags = "";
        
        // Test for 0 length element name
        if (strlen($elementName) == 0) {
            throw new \Qi\Form\Exception ("Element: Name not set.");
        } else {
            // Set element name
            $this->elementName = $elementName;
        }
        
        // Set name property
        $properties["name"] = $name;
        
        // Force properties to be an array
        // if (!is_array($properties)) $properties = array();
        
        // Set all other properties
        if (is_array($properties) && count($properties) > 0) {
            foreach ($properties as $key => $value) {
                $this->$key = $value;
            }
        }
        
        // If an id isn't set, and the auto-id value isn't set to false,
        // Render an auto-generated id (based on name value)
        if( !(isset($properties["auto-id"]) && $properties["auto-id"] === false) ){
            if (is_null($this->id)) {
                $this->id = $this->name;
            }
        }
    }

    /**
     * Destructor
     */
    public function __destruct() {
        unset($this->properties);
        unset($this->attributes);
        unset($this->extraClasses);
        unset($this->eventListeners);
    }
    
    /**
     * Getter override - gets from properties collection
     * @return the property requested
     * @param $key String
     */
    public function __get($key) {
        if (array_key_exists($key, $this->properties)) {
            switch ($key) {
                case "prefix":
                    return substr($this->properties[$key], 0, strlen($this->properties[$key])-1);
                    break;
                case "label":
                    $label = $this->properties[$key];
                    if ($label && isset($this->properties["required"]) && $this->properties["required"] == true
                        && (!isset($this->properties["required-symbol"]) 
                        || $this->properties["required-symbol"] !== false)) $label .= "*";
                    return $label;
                default:
                    return $this->properties[$key];
            }
        }
        return null;
    }
    
    /**
     * Setter override - sets to the properties collection.
     * Can also be used to add a javascript event listener to the element.  
     * Event listeners are rendered as HTML event attributes, such as "onfocus" 
     * or "onchange".  The value should be set as a javascript function call or 
     * snippet to be executed when the event is triggered.
     * @param $key Object
     * @param $value Object
     */
    public function __set($key, $value) {
        // Lowercase all keys
        $key = strtolower($key);
        
        // Encode special characters for XML
        switch ($key) {
            case "value":
            case "tags":
            //case "label":
            case "script":
                break;
            default;
                // DISABLE
                $value = self::encode($value);
        }
        
        // Set property value for all keys
        $this->properties[$key] = $value;
        
        // Set appropriate attributes and extended attributes as applicable
        switch ($key) {
            // TODO - add type-specific settings
            
            // Identity Attributes
            
            case "id":
            case "name":
                // Do nothing - the id and name attributes are set at render time
                break;

            case "prefix":
                // Set prefix for element name and id
                array_push($this->prefixes, $value);
                break;
            
                
            // Form Attributes
            case "action":
            case "method":
            case "enctype":
            
            
            // Element Attributes
            case "type":
            case "class":
            case "style":
            case "title":
            case "size":
            case "rows":
            case "cols":
            case "for":
                $this->attributes[$key] = self::encodeAttribute($value);
                break;
            
            case "value":
                switch ($this->elementName) {
                    case \Qi\Form\Enum\ElementType::SELECT:
                    case \Qi\Form\Enum\ElementType::TEXTAREA:
                    case \Qi\Form\Enum\ElementType::DIV:
                    case \Qi\Form\Enum\ElementType::SPAN:
                        break;
                    default:
                        $this->attributes[$key] = self::encodeAttribute($value);
                }
                break;
            
            // Events
            case \Qi\Form\Enum\EventType::LOAD:
            case \Qi\Form\Enum\EventType::UNLOAD:
            case \Qi\Form\Enum\EventType::KEY_DOWN:
            case \Qi\Form\Enum\EventType::KEY_UP:
            case \Qi\Form\Enum\EventType::KEY_PRESS:
            case \Qi\Form\Enum\EventType::MOUSE_OVER:
            case \Qi\Form\Enum\EventType::MOUSE_OUT:
            case \Qi\Form\Enum\EventType::MOUSE_DOWN:
            case \Qi\Form\Enum\EventType::MOUSE_UP:
            case \Qi\Form\Enum\EventType::CLICK:
            case \Qi\Form\Enum\EventType::DOUBLE_CLICK:
            case \Qi\Form\Enum\EventType::FOCUS:
            case \Qi\Form\Enum\EventType::BLUR:
            case \Qi\Form\Enum\EventType::PRESS:
            case \Qi\Form\Enum\EventType::RELEASE:
            case \Qi\Form\Enum\EventType::CHANGE:
            case \Qi\Form\Enum\EventType::SUBMIT:
            case \Qi\Form\Enum\EventType::RESET:
                $this->addEvent($key, $value);
                break;
            
            
                
            // Boolean Attributes
            case "disabled":
            case "multiple":
            case "selected":
            case "checked":
            case "readonly":
                if ($value) {
                    $this->attributes[$key] = $key;
                }
                break;
            
            
            // Quasi-Boolean Attributes
            case "autocomplete":
                if ($value === false || $value == "off") {
                    $this->attributes[$key] = "off";
                }
                break;
                
                
            // Extended Attributes
            case "markup":
                $this->addClass("qf-markup-{$value}");
                break;
                
            case "minlength":
                $this->addClass("qf-minlength-{$value}");
                break;
                
            case "maxlength":
                $this->attributes[$key] = self::encodeAttribute($value);
                $this->addClass("qf-maxlength-{$value}");
                break;
                
            case "required":
                if ($value == true) {
                    $this->addClass("qf-required");
                }
                break;
                
            case "minimum":
                $this->addClass("qf-minimum-{$value}");
                break;
                
            case "format":
                $value = HtmlRegex::encode($value);
                $this->addClass("qf-format-{$value}");
                break;
                
            case "mask":
                $value = HtmlRegex::encode($value);
                $this->addClass("qf-mask-{$value}");
                break;
                
            case "confirm":
                // Moved to getNode
                //$this->addClass("qf-confirm-{$value}");
                break;
            
            
            // Appended Element Properties
            case "label":
                // Label node
                
                // Default title to equal label
                if (!strlen($this->title)) $this->title = $value;
                break;
                
            case "content":
                $this->content = $value;
                break;
                
            case "info":
                break;
                
            case "prompt":
                // Prompt value for Select (default), Checkbox, Radio, and Option
                // Watermark value for Text, Password, and Date Inputs
                break;
                
            default:
                // Unrecognized
                break;
        }
    }
    
    /**
     * Override the __toString functionality to return an XHTML String
     * from the \DOMDocument
     */
    public function __toString() {
        // Get the fully populated node with all children
        return $this->getNode();
    }
    
    /**
     * Return the html string
     * @return string
     */
    public function &getNode(array $additional = array()) {
        
        $node = "<{$this->elementName}";

        // Set name and id attributes
        if ($this->elementName != "div") $this->attributes["name"] = $this->getName();
        
        if ($this->multiple == true) {
            $this->attributes["name"] .= "[]";
        }
        
        $this->attributes["id"] = $this->getId();
        
        // Concatenate the internal functional classes and append the Qi Forms 
        // element-specific class and user-supplied element styling class at the end
        $classes = implode(" ", $this->extraClasses) . " qf-{$this->elementName} {$this->class}";
        if ($this->confirm) {
            $confirm = "";
            if (is_object($this->confirm)) {
                $confirm = $this->confirm->getId();
            } else {
                if (substr($this->confirm, 0, 3) == "qf_") {
                    $confirm = $this->confirm;
                } else {
                    // Get element by name and confirm it
                    $confirm = "qf_" . $this->confirm;
                }
            }
            $classes .= " qf-confirm-{$confirm}";
        }
        $this->attributes["class"] = trim($classes);
        
        // Add node attributes
        foreach($this->attributes as $key => $value) {
            if (!is_null($value) && !is_array($value)) {
                $value = addslashes($value);
                $node .= " {$key}=\"{$value}\"";
            }
        }
        
        // Set the browser events
        foreach ($this->events as $eventType => $handlers) {
            $handlers = implode("; ", $handlers);
            $node .= " {$eventType}=\"{$handlers}\"";
        }
        
        $node .= $this->content ? ">{$this->content}</{$this->elementName}>" : " />";
                
        // Return the node
        return $node;
        
    }
    
    
    /**
     * Get the Element's \DOMDocument
     * @return \DOMDocument
     */
    public function &getDom() {
        // Initialize \DOM Document
        if (get_class($this->dom) != "\DOMDocument") {
            $this->dom = new \DOMDocument("1.0", "utf-8");
            $this->dom->formatOutput = true;
        }
        
        // Return the persisted \DOM Document
        return $this->dom;
    }
    

    /**
     * Append to the beginning of the class attribute
     * Used to pass additional information to the validation script
     */
    public function addClass($newClass) {
        array_push($this->extraClasses, $newClass);
        return $this;
    }
    
    /**
     * Append to the node's browser event handlers
     * @param $eventType EventType The browser event to listen for
     * @param $handler String The javascript code to execute on event dispatch
     */
    public function addEvent($eventType, $handler) {
        if (!array_key_exists($eventType, $this->events)) {
            $this->events[$eventType] = array();
        }
        array_push($this->events[$eventType], $handler);
        return $this;
    }
    
    /**
     * Append to the beginning of the element prefix
     * Used to define heirarchical organization of Elements
     */
    public function addPrefix($prefix=null) {
        array_push($this->prefixes, $prefix);
        return $this;
    }
    
    /**
     * Render a prefix based on the local prefix collection
     * @return String The rendered prefix
     */
    public function getPrefix() {
        $prefix = "";
        if (!empty($this->prefixes)) {
            $prefix = implode("__", array_reverse($this->prefixes)) . self::PREFIX_SEPARATOR;
        }
        return $prefix;
    }
    
    /**
     * Render an id based on the element name
     * @return String the rendered id
     */
    public function getId() {
        if ($this->id && $this->noid !== true) {
            // Return id attribute for all except option
            switch (strtolower($this->elementName)) {
                case "option":
                    // TODO - Implement a method to determine which attributes an element supports
                    return null;
                default:
                    return "qf_" . $this->getPrefix() . $this->id;
            }
        }
    }
    
    public function getName() {
        // Add name attribute for input, select and textarea only
        if ($this->elementName == "input" ||
            $this->elementName == "select" ||
            $this->elementName == "textarea" ||
            $this->listType == \Qi\Form\Enum\ListType::CHECKBOX ||
            $this->listType == \Qi\Form\Enum\ListType::RADIO) {
                return ($this->noprefix == true ? "" : $this->getPrefix()) . $this->name;
        }
    }
        
    /**
     * Event handling method for internal callbacks
     * This is not to be used for browser events.
     * Use "addEvent" to add javascript browser event handlers
     * @param $event Object The event to listen for
     * @param $obj Object The object containing the callback method
     * @param $callbackFunction Object The callback method
     */
    public function addEventListener($event, &$obj, $callbackFunction) {
        $callback = array($obj, $callbackFunction);
        $this->eventListeners[$event] = $callback;
        return $this;
    }

    /**
     * Dispatch an event with args
     * @param $event Object The event to dispatch
     * @param $args var The arguments to be passed to the callback function
     */
    protected function dispatchEvent($event, $args=null) {
        if (!is_array($this->eventListeners)) return;
        $callback = isset($this->eventListeners[$event]) ? $this->eventListeners[$event] : null;

        if (isset($callback) && is_array($callback) && count($callback) == 2 && is_object($callback[0])) {
            if (is_array($args)) {
                call_user_func_array($callback, $args);
            } else if (!is_null($args)) {
                call_user_func($callback, $args);
            } else {
                call_user_func($callback);
            }
        }
        return $this;
    }
    
    /**
     * Generate a label node for this element
     * @return \DOMNode Label element
     * @param $dom Object
     * @param $for Object
     * @param $title Object
     */
    function generateLabel(\DOMDocument &$dom) {
        if (is_null($this->label) || $this->label == "") return null;
        $label = self::xmlDecode($this->label);
        if ($label != $this->label && strpos($label, "<") !== false && strpos($label, ">") !== false) {
            // If the label contains HTML, parse it into a node
            $d = new DomDocument();
            $d->loadXML("<label>{$label}</label>");
            $d->preserveWhiteSpace = false;
            $node = $dom->importNode($d->documentElement, true);
        } else {
            // Otherwise, just make a plain text label node 
            $node = $dom->createElement("label", $this->label);
        }
        $node->setAttribute("id", $this->getId() . self::PREFIX_SEPARATOR . "label");
        $node->setAttribute("for", $this->getId());
        $spanNode = $dom->createElement("span");
        $spanNode->setAttribute("class", "qf-label-span");
        $spanNode->setAttribute("id", $this->getId() . self::PREFIX_SEPARATOR . "label_span");
        $spanNode->appendChild($node);
        if ($this->tags) {
            $node2 = $dom->createElement("p", "Allowed HTML: ". $this->tags);
            $node2->setAttribute("style", "font-size: 8pt; color: #aaaaaa; margin: 0 0 2px");
            $spanNode->appendChild($node2);
        }
        if ($this->info) {
            $node2 = $dom->createElement("p", $this->info);
            $node2->setAttribute("style", "font-size: 9pt; color: #666; margin: 0 0 2px");
            $spanNode->appendChild($node2);
        }
        return $spanNode;
    }
    
    /**
     * Generate a popup info dialog that is shown on focus of an element
     * and hidden on blur
     * @return \DOMNode Info dialog element
     * @param $info String The information to display in the dialog
     */
    protected function generateInfoDialog($info) {
        // Add the focus and blur listeners
        $this->addEvent(\Qi\Form\Enum\EventType::FOCUS, "showFocusInfo({$infoId}, this)");
        $this->addEvent(\Qi\Form\Enum\EventType::BLUR, "hideFocusInfo({$infoId}, this)");
        
        // Create the info dialog with help text
        $this->info = $this->dom->createElement("div");
        $this->info->appendChild($this->dom->createElement("p", $this->info));
        $this->info->setAttribute("id", $this->getId() . "_info");
        $this->info->setAttribute("title", $this->title . " Info");
        $this->info->setAttribute("class", "qf-info-box");
        //$this->info->setAttribute("style", "display: none;");
        return $this;
    }
    
    /**
     * Load data from the element into an array or an object
     * @param $data Data The collection of data to load from
     * @return String The loaded value
     */
    public function getData($data) {
        $name = $this->getName();
        $value = null;
        if (is_array($data) && isset($data[$name])) {
            $value = $data[$name];
        } else if (is_object($data)) {
            $value = $data->$name;
        }
        if (isset($value) && strlen($value) > 0) {
            $this->value = $value;
        }
        switch ($this->format) {
            case InputFormat::NUMERIC:
            case InputFormat::INT:
            case InputFormat::SSN:
            case InputFormat::PHONE:
            case InputFormat::EMAIL:
            case InputFormat::CCDATE:
                // Don't preformat these
                break;
            case InputFormat::PERCENTAGE:
                break;
            case InputFormat::CCNUMBER:
                $value = str_replace("-", "", $value);
                if (strlen($value) != 16) {
                    // Invalid?
                }
                break;
            case InputFormat::DOB:
                // Change format from mm/dd/yyyy to yyyy-mm-dd
                $parts = explode("/", $value);
                if (count($parts) == 3) {
                    $value = $parts[2]."-".$parts[0]."-".$parts[1];
                }
                break;
        }
        /*
        if ($this->format == InputFormat::PERCENTAGE) {
            if (strpos($value))
        }
        */
        if (isset($value) && !is_array($value) && $this->tags != "*") {
            $value = strip_tags($value, $this->tags);
        }
        return isset($value) ? $value : null;
    }
    
    /**
     * Load data into the element from an array or an object
     * @param $data Array The collection of data to load from
     */
    public function setData($data) {
        switch ($this->format) {
            case InputFormat::PERCENTAGE:
                break;
            case InputFormat::CCNUMBER:
                if (strlen($data) == 16) {
                    $data = substr($data, 0, 4)."-".substr($data, 4, 4)."-".substr($data, 8, 4)."-".substr($data, 12, 4);
                }
                break;
            case InputFormat::DOB:
                // Change format from mm/dd/yyyy to yyyy-mm-dd
                if (get_class($data) == "DateTime" || get_class($data) == "QiDateTime" || get_class($data) == "QiDate") {
                    $data = $data->format("m/d/Y");
                } else {
                    $parts = explode("-", $data);
                    if (count($parts) == 3) {
                        $data = $parts[1]."/".$parts[2]."/".$parts[0];
                    }
                }
                break;
        }
        $this->setValue($data);
        return $this;
    }
    
    public function setValue($value) {
        $this->value = $value;
        return $this;
    }
    
    
    public static function encodeAttribute($value) {
        $value = self::decode($value);
        return self::xmlEncode($value);
        return $this;
    }
    
    public static function encode($value) {
        if (is_string($value) && !is_numeric($value)) {
            $value = str_replace("&", "[!AMP!]", $value);
            $value = str_replace("<", "[!LT!]", $value);
            $value = str_replace(">", "[!GT!]", $value);
            $value = str_replace("\"", "[!QUOT!]", $value);
        }
        return $value;
    }
    
    public static function decode($value) {
        if (is_string($value) && !is_numeric($value)) {
            $value = str_replace("[!AMP!]", "&", $value);
            $value = str_replace("[!LT!]", "<", $value);
            $value = str_replace("[!GT!]", ">", $value);
            $value = str_replace("[!QUOT!]", "\"", $value);
        }
        return $value;
    }
    
    
    // Deprecated XML encoding functions
    public static function xmlEncode($value) {
        if (is_string($value) && !is_numeric($value)) {
            $value = str_replace("&", "&amp;", $value);
            $value = str_replace("<", "&lt;", $value);
            $value = str_replace(">", "&gt;", $value);
            //$value = str_replace("'", "&apos;", $value);
            $value = str_replace("\"", "&quot;", $value);
            $value = str_replace("&amp;amp;", "&amp;", $value);
            $value = str_replace("&amp;lt;", "&lt;", $value);
            $value = str_replace("&amp;gt;", "&gt;", $value);
            //$value = str_replace("&amp;apos;", "&apos;", $value);
            $value = str_replace("&amp;quot;", "&quot;", $value);
        }
        return $value;
    }
    
    public static function xmlDecode($value) {
        if (is_string($value) && !is_numeric($value)) {
            $value = str_replace("&amp;", "&", $value);
            $value = str_replace("&lt;", "<", $value);
            $value = str_replace("&gt;", ">", $value);
            $value = str_replace("&quot;", "\"", $value);
        }
        return $value;
    }
}
