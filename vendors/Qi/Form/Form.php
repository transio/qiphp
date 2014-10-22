<?php
namespace Qi\Form;

/**
 * The Form class represents a top-level form container.
 */
class Form extends Model\Container
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
    
    public function &getNode(array $properties=array())
    {
        // TODO - get the Resource object via DI
        $this->action = empty($this->action) ? "" : $this->action;
        $this->addEvent(Enum\EventType::SUBMIT, "return validate(this)");
        $this->addElement(new HiddenInput("qf", $this->checksum, array("noid" => true, "noprefix" => true)));
        return parent::getNode($properties);
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
     * @param $data String [optional] 
     * @param $format DataFormat [optional]
     */
    public function getData($data=null, $format=\Qi\Archetype\Enum\DataFormat::ASSOC_ARRAY)
    {
        // Retrieve the data based upon form method
        $data = null;
        switch ($this->method) {
            case \Qi\Http\Method::POST:
                $data = $_POST;
                break;
            case \Qi\Http\Method::GET:
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
            case \Qi\Archetype\Enum\DataFormat::ASSOC_ARRAY:
            case \Qi\Archetype\Enum\DataFormat::OBJECT:
            case \Qi\Archetype\Enum\DataFormat::XML:
            case \Qi\Archetype\Enum\DataFormat::JSON:
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
            $this->enctype = \Qi\Enum\MimeType::MULTIPART_FORM_DATA;
        }
    }

}
