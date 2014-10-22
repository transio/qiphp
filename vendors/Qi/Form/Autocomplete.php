<?php
namespace Qi\Form\Helpers;

class Autocompose extends Autocomplete {
        // Autocompose
        if (is_array($this->autocompose)) {
            // Required properties
            $id = $this->getId();
            $options = json_encode($this->autocompose);
            $js = <<<JS
                // {$this->name} Autocompose Script
                $("#{$id}").autocompose({$options});
JS;
            InlineJs::add($js);
        }
}

class Autocomplete extends Helper {
    public function get
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
            InlineJs::add("$('${$id}').autocomplete({'source': '{$source}' {$options}});");
        }
    }
