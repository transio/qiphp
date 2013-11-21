<?php
namespace Qi\Mail;

/**
 * The Template class is used to format Mail objects with display data
 */
class Template
{
    private $templateName = null;
    private $template = null;
    private $hostName = null;
    
    public function __construct($templateName=null, $mimeType=MimeType::HTML)
    {
        // Load Global Settings
        global $settings;
        
        // Set the template file
        $this->setTemplate($templateName, $mimeType);
        
        // Make $this->content a stdClass Object and initialize it's default values
        $this->content = new stdClass();
    }

    public function setTemplate($templateName=null, $mimeType=MimeType::HTML)
    {
        // Load Global Settings
        global $settings;
        
        $templateFile = "";
        
        // Get the file name from the mime type
        switch ($mimeType) {
            case MimeType::MULTIPART_ALTERNATIVE:
                $templateFile = "multipart-alternative";
                break;
            case MimeType::TEXT:
                $templateFile = "text";
                break;
            case MimeType::RTF:
                $templateFile = "rtf";
                break;
            case MimeType::HTML:
            default:
                $templateFile = "html";
                break;
        }
        
        // Check if the template exists
        $templateFile = $settings->path->emailTemplates . DIRECTORY_SEPARATOR . $templateName . DIRECTORY_SEPARATOR . $templateFile . ".tpl";
        
        if (file_exists($templateFile)) {
            $this->templateName = $templateName;
            $this->template = $templateFile;
            $this->uri = $settings->uri->emailTemplates . $templateName . "/";
        } else {
            throw new Exception("Email template not found: " . $templateName);
        }
    }
    
    public function renderMessage($message, stdClass &$params=null)
    {
        // If the template exists, render it
        if (file_exists($this->template)) {
            // Start body output buffer
            ob_start();
            
            // Render the template
            include_once($this->template);
            $content = ob_get_contents();
        
            // End the output buffer
            ob_end_clean();
            
            // Return the rendered contents
            return $content;
        } else {
            throw new Exception("template_not_found");
        }
    }
}