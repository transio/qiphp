<?php
namespace Qi\Form;

/**
 * The TextInput class represents a text input form element
 */
class TextInput extends Input
{
    private $autocomplete;
    
    /**
     * Constructor
     * @param $name String
     * @param $title String
     * @param $properties Array[optional]
     */
    public function __construct($name, array $properties=null)
    {
        parent::__construct(InputType::TEXT, $name, $properties);
        if (isset($properties["autocomplete"]))
            $this->autocomplete = $properties["autocomplete"];
    }
    
    public function &getNode(DOMDocument &$dom=null)
    {
        // Autocompose
        if (is_array($this->autocompose)) {
            // Required properties
            $id = $this->getId();
            $options = json_encode($this->autocompose);
            $GLOBALS["settings"]->html->loadScript .= <<<JS
                // {$this->name} Autocompose Script
                $("#{$id}").autocompose({$options});
JS;
        }
        
        // If autocomplete, add the header script
        if (is_array($this->autocomplete)) {
            // Required properties
            $id = $this->getId();
            $method = $this->autocomplete["source"] ? $this->autocomplete["source"] : $this->name;
            $source = "/_/autocomplete?method={$method}";
            $nameCol = isset($this->autocomplete["name"]) ? $this->autocomplete["name"] : "name";
            $idCol = isset($this->autocomplete["id"]) ? $this->autocomplete["id"] : null;
            
            // Options
            $options = "";
            foreach ($this->autocomplete as $key => $value) {
                switch ($key) {
                    case "source":
                    case "name":
                    case "id":
                    case "multi":
                        break;
                    case "select":
                                                $options .= ",\n\t\t\t\t'{$key}': {$value}";
                        break;
                    default:
                        if (!is_numeric($value)) $value = "'".mysql_real_escape_string($value)."'";
                        $options .= ",\n\t\t\t\t'{$key}': {$value}";
                        break;
                }
            }
            
            $GLOBALS["settings"]->html->loadScript .= <<<JS
            
                // {$this->name} Autocomplete Script
                $("#{$id}").autocomplete({
                    "source": "{$source}"
                    {$options}
                });
JS;
        }
        
        return  parent::getNode($dom);
    }
}
