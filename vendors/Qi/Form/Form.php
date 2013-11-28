<?php
namespace Qi\Form;

use Enum\InputType;
use Enum\ValidationType;
use \Qi\Enum\DataFormat;
use \Qi\Enum\MimeType;

/**
 * The Form class represents a top-level form container.
 */
class Form extends Container
{
    private $checksum;
    
    /**
     * Constructor
     * @param $name String The name of the form
     * @param $properties Array[optional] The form's properties (See Element for details on options)
     */
    public function __construct($name, array $properties=null) {
        parent::__construct("form", $name, $properties);
        if (isset($properties["checksum"])) {
        $this->checksum = $properties["checksum"];
    } else {
        $this->checksum = crc32($this->name);
    }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
    }
    
    public function &getNode(DOMDocument &$dom=null) {
    
        // TODO - implement cmoponent to generate unique ID for all form elements
        // even if they have the same $name parameter
        global $uri;
        $this->action = is_null($this->action) ? $uri->current() : $this->action;
        
        switch ($this->validation) {
            // TODO - Implement multi-validation ($validation would be an array)
            case ValidationType::BROWSER:
                $this->addEvent(EventType::SUBMIT, "return validate(this)");
                break;
            case ValidationType::SERVER:
                // TODO - Implement server-side form validation
                break;
            case ValidationType::AJAX:
                // TODO - Implement ajax form validation
                break;
            default:
                // Default - no validation
                break;
        }
        
        // Ajax Options:
        //target:    selector   // target element(s) to be updated with server response 
        //success:   function   // post-submit callback 
        //url:       url        // override for form's 'action' attribute 
        //type:      type       // 'get' or 'post', override for form's 'method' attribute 
        //dataType:  null       // 'xml', 'script', or 'json' (expected server response type) 
        //clearForm: true       // clear all form fields after successful submit 
        //resetForm: true       // reset the form after successful submit 
        //timeout:   milli 
        if (is_array($this->ajax)) {
            global $settings;
            //array_push($settings->html->scripts, new HtmlScript("scripts/jquery/jquery.form.js"));
            $id = $this->getId();
            $options = "";
            foreach ($this->ajax as $key => $value) {
                switch ($key) {
                    case "success":
                    case "clearForm":
                    case "resetForm":
                    case "timeOut":
                         // Non-String datatype
                        break;
                    case "target":
                    case "url":
                    case "type":
                    case "dataType":
                    default;
                        $value = "'{$value}'";
                        break;
                }
                $options .= "'{$key}': {$value}, ";
            }
            $settings->html->loadScript .= <<<JS
            
                // Form {$id} ajax handler
                $("#{$id}").ajaxForm({
                    {$options}
                    beforeSubmit: ajaxValidate
                });
                
JS;
        }
        
        $this->addElement(new HiddenInput("qf", $this->checksum, array("noid" => true, "noprefix" => true)));
        
        return parent::getNode($dom);
    }
    
    
    /**
     * Check the form's posted status for conditional actions
     * Posted status is based on the form's internal checksum input
     * @return Boolean true = form has been posted, false = form has not been posted
     */
    // TODO - change name to "submitted()" ?
    public function posted()
    {
        return isset($_REQUEST["qf"]) && ($_REQUEST["qf"] == $this->checksum);
    }
    
    /**
     * Server-side validation (for both synchronous an AJAX validation calls)
     * @return 
     */
    public function validate()
    {
        // TODO - Develop this
    }
    
    /**
     * Retrieve data from the form into an object
     * @return var Data
     * @param $
     * @param $fieldset String[optional] 
     * @param $format DataFormatType[optional]
     */
    public function getData($data=null, $format=DataFormat::ASSOC_ARRAY)
    {
        // Retrieve the data based upon form method
        $data = null;
        switch ($this->method) {
            case HttpRequestMethod::POST:
                $data = $_POST;
                break;
            case HttpRequestMethod::GET:
                $data = $_GET;
                break;
            default:
                $data = $_REQUEST;
                break;
        }
        
        // Parse the data into a heirarchical array
        $data = parent::getData($data);
        
        // Return the appropriate data formate
        // TODO - implement?
        switch ($format) {
            case DataFormat::ASSOC_ARRAY:
            case DataFormat::OBJECT:
            case DataFormat::XML:
            case DataFormat::JSON:
            default:
                return $data;
                break;
        }
    }
    

    /**
     * Override Container->onChildContainerAddElement
     * Check for FileInput objects and change the enctype accordingly
     */
    public function onChildContainerAddElement(&$args)
    {
        // Check for File Inputs
        if (get_class($args) == "FileInput") {
            // Set the enctype to multipart-form
            $this->enctype = MimeType::MULTIPART_FORM_DATA;
        }
    }

}