<?php
namespace Qi\Html;

/**
 * The HtmlBlock abstracts one block in an HtmlTemplate, which is used to lay out
 * block modules on specific page(s).
 */
class Block
{
    const RENDER_ALL_EXCEPT = 0x0;
    const RENDER_ONLY = 0x1;
    
    public $moduleName;
    public $renderType;
    public $modules;
    public $template;
    
    public function __construct($moduleName, array $options = array())
    {
        $defaults = array(
            "render_type" => self::RENDER_ALL_EXCEPT, 
            "modules" => null, 
            "template" => "block"
        );
        $options = array_merge($defaults, $options);
        
        $this->moduleName = $moduleName;
        $this->renderType = $options["render_type"];
        $this->modules = $options["modules"];
        $this->template = $options["template"];
    }
}
