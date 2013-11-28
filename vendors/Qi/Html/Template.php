<?php

/**
 * The HtmlTemplate class is used to generate HTML from Qi Templates
 */
class Template {
    // Page Name
    private $pageName;
    
    // Template name
    private $templateName;
    
    // The URI of the template root
    private $templateUri;
    
    // Template stdClass stores template settings
    private $template;
    
    private $uri;
    
    private $request;
    
    // Page Head stdClass stores core page head data
    public $pageHead;
    
    // Content stdClass stores content generated here
    public $content;
    
    /**
     * Constructor
     * @param $templatePath Object
     */
    public function __construct($template) {
        // Load Global Settings
        global $settings;
        
        $this->uri = $uri;
        $this->request = $request;
        
        // Set the template name from the Qi settings
        foreach ($settings->html->templates as $templateName => $matches) {
                    if (!is_array($matches)) $matches = array($matches);
                    foreach ($matches as $match) {
            if (!strlen($match) || strtolower(substr($uri->executor, 0, strlen($match))) == strtolower($match)) {
                // If a match is found, break the loop
                // The last template in the array is the default
                break;
            }
                    }
        }
        $this->templateName = $templateName;

        // Set the default page head data
        if(!isset($this->pageHead)) $this->pageHead = new stdClass();
        $this->pageHead->title = "";
        $this->pageHead->meta = $settings->html->meta;
        
        $this->templateUri = "{$settings->uri->base}templates/{$templateName}/";
        // Get the template settings
        $templateFile = $settings->path->www . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $this->templateName . DIRECTORY_SEPARATOR . "template.php";
        if (file_exists($templateFile)) {
            // Load the template settings file into the template property
            include_once($templateFile);
            $this->template = $template;
        } else {
            $message = $this->renderMessage("Template not found ({$templateName})", $messageType=MessageType::ERROR);
            print($message);
            exit;
        }
                
        // Make $this->content a stdClass Object and initialize it's default values
        $this->content = new stdClass();
        $this->content->body = "";
        $this->content->blocks = array();
    }
    
    /**
     * Get the template content object
     * @return TemplateContent the content object
     */
    public function getContent() {
        return $this->content;
    }
    
    /**
     * Load a module's page
     * @param $moduleName String the module to load
     */
    public function loadPage(stdClass &$params=null) {
        // Load Global Settings
        global $settings;
        global $mli;
        
        // Set the page name
        $this->pageName = $this->uri->executor;

        // Set the body parameters
        $params->name = $this->uri->executor;
        
        // Get the module path
        $modulePath = $settings->path->app . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . $this->uri->executor . DIRECTORY_SEPARATOR . $this->uri->action . ".php";
        
        // Load / Render the Page Body
        $content = "";
        $this->content->messages = "";
        try {
            // Check pending messages
            //$this->content->messages = $this->renderMessages();
            
            // Load the content / body
            $content = $this->loadModule($modulePath, $params);
            
        } catch (Exception $e) {
            $this->content->title = "Error";
            Log::writeLn($e->getMessage() . " ({$modulePath})", Log::ERROR);
            MessageManager::addMessage($e->getMessage(), $messageType=MessageType::ERROR);
            if (isset($settings->testMode) && get_class($settings->testMode) == "TestMode") {
                $content .= "<div style=\"overflow:auto;height:400px\">";
                foreach ($e->getTrace() as $i => $error) {
                    $class = isset($error["class"]) ? $error["class"] : "";
                    $type = isset($error["type"]) ? $error["type"] : "";
                    $function = isset($error["function"]) ? $error["function"] : "";
                    $content .= "<p><strong>[{$i}] {$class}{$type}{$function}()</strong> ". (isset($error['file']) ? $error['file'] : "") . " line " . (isset($error['line']) ? $error['line'] : "") . ") ";
                    $content .= "<strong>Args:</strong><br /><blockquote><pre>";
                    if (isset($error["args"]) && is_array($error["args"]))
                    foreach ($error["args"] as $arg => $val) {
                        if (is_a($val, "Archetype")) {
                            $name = $val->getName();
                            $content .= "{$arg} = {$name}(";
                            foreach ($val->getData() as $key => $value) {
                                $content .= "{$key} => {$value}";
                            }
                            $content .= ")";
                        } else {
                            ob_start();
                            ob_implicit_flush(0);
                            print_r($val);
                            $val = ob_get_contents();
                            ob_end_clean();
                            $content .= "{$arg} = {$val}<br />";
                        }
                    }
                    $content .= "</pre></blockquote></p>";
                }
                $content .= "</div>";
            } else {
                $content = "<p>".$e->getMessage()."</p>";
            }
        }
        
        try {
            // Check new messages / errors thrown by page load
            $this->content->messages .= $this->renderMessages();
        } catch (Exception $e) {
        }
        
        $this->content->body = $content;
        

        
        // Update the page head title
        $this->pageHead->title = isset($this->pageHead->title) && $this->pageHead->title ? $this->pageHead->title : (isset($this->content->title) && $this->content->title ? $this->content->title : $settings->html->title);
        if (isset($settings->html->titleName) && $settings->html->titleName) $this->pageHead->title .= " | {$settings->html->titleName}";
        $this->pageHead->title = strip_tags($this->pageHead->title);
        
        // Load the blocks
        if (is_array($settings->blocks)) {
            foreach ($settings->blocks as $regionName => &$region) {
                // Get the region for the block on the current page
                if (is_array($region)) {
                    foreach ($region as $module => &$block) {
                        if (is_array($block)) 
                            $block = new HtmlBlock($module, $block);
                        else if (!($block instanceof HtmlBlock))
                            continue;
                                                
                        // If the block is supposed to be rendered on this page
                        $A = $block->renderType == HtmlBlock::RENDER_ONLY;  // Is the block a RENDER_ONLY render-type?
                        // Does the current Uri match in the block's patterns?
                        $B = false;
                        if (!empty($block->modules)) {
                            foreach ($block->modules as $pattern)
                                if (is_object($this->request)) {
                                    $B = $B || $this->request->matches($pattern);
                                } else if (is_object($this->uri)) {
                                    $B = $B || $this->uri->matches($pattern);
                                }
                        }
                        if (!($A xor $B)) {
                            // Load the block in the correct region
                            $params = (object) $params;
                            $this->loadBlock($block, $regionName, $params);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Load a module into a block
     * @param $moduleName String the module to load
     * @param $region String the region identifier
     */
    public function loadBlock(&$block, $regionName, stdClass &$params=null) {
        // Load Global Settings
        global $settings;
        
        // Set the block parameters
        $params->title = "";
        $params->name = $block->moduleName;
        $params->template = $block->template;
        
        // Get the module path
        
        // Load / Render the block
        try {
            $cache =& $settings->cache->block[$block->moduleName];
            $cachable = get_class($cache) == "HtmlCache";
            if ($cachable && $cache->isCached() && !$cache->isExpired()) {
                $content = $cache->read();
            } else {
                $blockPath = $settings->path->app . DIRECTORY_SEPARATOR . "blocks" . DIRECTORY_SEPARATOR . $block->moduleName . ".php";
                $content = $this->loadModule($blockPath, $params);
                if ($cachable) $cache->write($content);
            }
        } catch (Exception $e) {
            $content = $e->getMessage();
        }

        // Append the block to the region's blocks
        $this->addBlock($content, $regionName);
        
    }
    
    /**
     * Load a module into a block
     * @param $blockContent String the block html
     * @param $region String the region identifier
     */
    public function addBlock($content, $regionName) {
        
        // Append the block to the region's blocks
        if(!isset($this->content->blocks[$regionName])) {
            $this->content->blocks[$regionName] = "";
        }
        $this->content->blocks[$regionName] .= $content . "\n";
        
    }
    
    /**
     * Load a Module's Content
     * @return String the output of the module
     * @param $moduleName String the module to load
     */    
    private function loadModule($modulePath, stdClass &$params=null) {
        // Load Global Settings
        global $settings;
        
        // If the module exists, render it
        if (file_exists($modulePath)) {
            // Start body output buffer
            ob_start();
            
            // Render the page contents
            include($modulePath);
            
            // Get the body contents from the buffer
            $content = ob_get_contents();
    
            // End the output buffer
            ob_end_clean();
        } else {
            throw new Exception("The requested resource does not exist ({$modulePath}).");
        }
        
        // If a template is specified, render it.
        if (isset($params->template)) {
            $content = $this->renderSimpleContentTemplate($params->template, $content, $params);
        }
        
        // Return the content
        return $content;
    }
    
    public function renderSimpleContentTemplate($template, $content=null, stdClass &$params=null) {
        // Load Global Settings
        global $settings;
        
        // Get the template path
        $templatePath = $settings->path->www . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $this->templateName . DIRECTORY_SEPARATOR . $template . ".tpl";
            
        // If the template exists, render it
        if (file_exists($templatePath)) {
            // Start body output buffer
            ob_start();
            
            // Render the template
            include($templatePath);
            $content = ob_get_contents();
        
            // End the output buffer
            ob_end_clean();
        } else {
            //$content = $this->renderMessage("Template not found ({$this->templateName}/{$template})", $messageType=MessageType::ERROR);
            $content = "Template not found ({$this->templateName}/{$template})";
        }
        
        // Return the rendered content in the template
        return $content;
    }
    
    public function renderMessages($template="message") {
        $messageHtml = "";
        if (MessageManager::hasMessages()) {
            // Render messages
            foreach (MessageManager::getMessages() as $message) {
                $messageHtml .= $this->renderMessage($message->message, $message->type, $template);
            }
            // Purge messages
            MessageManager::purgeMessages();
        }
        return $messageHtml;
    }
    
    public function renderMessage($message, $messageType=MessageType::ALERT, $template="message") {
        global $settings;
        global $mli;
        
        // Try to retrieve the translated message from the settings file
        // Or defeult to the provided string if not found
        $message = MLI::getValue($message);
        if ($messageType == MessageType::ERROR) {
            $message = MLI::getValue($message);
            if (!$message) $message = MLI::getValue("exception_unhandled");
        }
        // Set the parameters for the message template rendering
        $params = new stdClass();
        $params->messageType = $messageType;
        
        // Render the message
        $content = $this->renderSimpleContentTemplate($template, $message, $params);
        
        // Return the rendered message
        return $content;
    }
    
    
    
    /**
     * Renders and outputs the template
     */
    public function render(array &$params=null) {
        // Load Global Settings
        global $settings;
        $templateUri = "{$settings->uri->absolute}templates/{$this->templateName}/";
        
        // Construct the content head
        $title = $this->pageHead->title ? $this->pageHead->title : ($this->content->title ? $this->content->title : $settings->html->title);
        $this->content->head = "<title>{$title}</title>\n";
        
        // Construct Meta Tags
        if (is_array($this->pageHead->meta)) {
            foreach ($this->pageHead->meta as $name => $content) {
                if ($name == "refresh") {
                    $length = 0;
                    if (is_array($content)) {
                        if (count($content) == 2) {
                            $length = $content[1];
                            $content = $content[0];
                        } else {
                            $content = $content[0];
                        }
                    }
                    $this->content->head .= "\t\t<meta http-equiv=\"{$name}\" content=\"{$length};url={$content}\" />\n";
                } else {
                    $this->content->head .= "\t\t<meta name=\"{$name}\" content=\"{$content}\" />\n";
                }
            }
        }
        $this->content->head .= "\t\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . (isset($this->template->charset) ? $this->template->charset : "utf-8") . "\" />\n";
        
        // Append core stylesheets to the head
        if (is_array($settings->html->stylesheets)) {
            foreach ($settings->html->stylesheets as $key => &$value) {
                if ($value instanceof HtmlStylesheet)
                    $stylesheet =& $value;
                else if (is_string($key) && is_array($value))
                    $stylesheet = new HtmlScript($key, $value);
                else if (is_numeric($key))
                    $stylesheet = new HtmlStylesheet($value);
                else
                    continue;
                
                $this->content->head .= $stylesheet->getHtml($settings->uri->base);
            }
        }

        // Append template-specific stylesheets to the head
        if (is_array($this->template->stylesheets)) {
            foreach ($this->template->stylesheets as $key => &$value) {
                if ($value instanceof HtmlStylesheet)
                    $stylesheet =& $value;
                else if (is_string($key) && is_array($value))
                    $stylesheet = new HtmlScript($key, $value);
                else if (is_numeric($key))
                    $stylesheet = new HtmlStylesheet($value);
                else
                    continue;
                
                $this->content->head .= $stylesheet->getHtml($templateUri);
            }
        }
        
        
        $loadedScripts = array();
        
        // Append core scripts to the head
        if (is_array($settings->html->scripts)) {
            foreach ($settings->html->scripts as $key => &$value) {
                if ($value instanceof HtmlScript)
                    $script =& $value;
                else if (is_string($key) && is_array($value))
                    $script = new HtmlScript($key, $value);
                else if (is_numeric($key))
                    $script = new HtmlScript($value);
                else
                    continue;
                
                $scriptHtml = $script->getHtml($templateUri);
                
                // This check is to avoid duplicates
                if (!in_array($scriptHtml, $loadedScripts)) {
                    array_push($loadedScripts, $scriptHtml);
                    $this->content->head .= $scriptHtml;
                }
            }
        }

        // Append template-specific scripts to the head
        if (is_array($this->template->scripts)) {
            foreach ($this->template->scripts as $key => &$value) {
                if ($value instanceof HtmlScript)
                    $script =& $value;
                else if (is_string($value) && is_array($value))
                    $script = new HtmlScript($value, $value);
                else if (is_numeric($key))
                    $script = new HtmlScript($value);
                else
                    continue;
                
                $scriptHtml = $script->getHtml($templateUri);
                
                // This check is to avoid duplicates
                if (!in_array($scriptHtml, $loadedScripts)) {
                    array_push($loadedScripts, $scriptHtml);
                    $this->content->head .= $scriptHtml;
                }
            }
        }
        
        // Append a literal script block (can be supplied by modules)
        if (isset($this->pageHead->script) || isset($settings->html->loadScript)|| isset($settings->html->script)) {
        $this->content->head .= "\t\t<script type=\"text/javascript\">\n\t\t//<![CDATA[\n";

        if (isset($this->pageHead->script)) $this->content->head .= $this->pageHead->script . "\n";
        if ($settings->html->script) $this->content->head .= $settings->html->script . "\n";

        if ($settings->html->loadScript) $this->content->head .= "
            \$(document).ready(function() {
                {$settings->html->loadScript}
            });\n\n";
        $this->content->head .= "\t\t//]]>\n\t\t</script>\n";
        }
        
        
        // Get the template path
        $templatePath = $settings->path->www . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $this->templateName . DIRECTORY_SEPARATOR . "page.tpl";
        
        // Render the template
        // TODO - Push Compressor code live
        if (true || isset($settings->compression) && $settings->compression === false) {
            include_once($templatePath);
        } else {
            ob_start();
            ob_implicit_flush(0);
            include_once($templatePath);
            $compressor = new Compressor(ob_get_contents());
            ob_end_clean();
            $compressor->render();
        }
    }
}