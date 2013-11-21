<?php
namespace Qi\Form\Enum;

/**
 * An enum of markup types for rich text editors.
 */
class MarkupType
{    
    const MARKDOWN = "markdown";
    const HTML = "html";
    const BBCODE = "bbcode";
    const TINYMCE = "tinymce";
    
    private function __construct() {}
}
