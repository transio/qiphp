<?php
namespace Qi\Asset;

/**
 * The Js class abstracts an HTML script asset
 */
class Js
{
    public $src;
    public $condition;
    public $extra;
    
    public function __construct($src, array $options = array())
    {
        $defaults = array(
            "condition" => null,
            "extra" => ""
        );
        $options = array_merge($defaults, $options);
        
        $this->src = $src;
        $this->condition = $options["condition"];
        $this->extra = $options["extra"];
    }
    
    public function getHtml($baseUri="/")
    {
        $src = preg_match("/^http[s]*:\/\/.*/", $this->src) ? $this->src : $baseUri . $this->src;
        $output = is_null($this->condition) ? "" : "\t\t<!--[if {$this->condition}]>\n";
        $output .= "\t\t<script {$this->extra} type=\"text/javascript\" src=\"{$src}\"></script>\n";
        $output .= is_null($this->condition) ? "" : "\t\t<![endif]-->\n";
        return $output;
    }
}
