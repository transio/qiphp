<?php

class Ajax extends Helper {
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
        
}