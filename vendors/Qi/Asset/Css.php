<?php
namespace Qi\Asset;

/**
 * The Css class abstracts an HTML stylesheet (link) asset
 */
class Css
{
    public $path;
    public $media;
    public $condition;
    
    public function __construct($path, array $options=array())
    {
        $defaults = array(
            "media" => null,
            "condition" => null
        );
        
        $options = array_merge($defaults, $options);
        
        $this->path = $path;
        $this->media = $options["media"];
        $this->condition = $options["condition"];
    }

    public function getHtml($baseUri="/")
    {
        $path = preg_match("/^http:\/\/.*/", $this->path) ? $this->path : $baseUri . $this->path;
        $output = is_null($this->condition) ? "" : "\t\t<!--[if {$this->condition}]>\n";
        $output .= "\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"{$path}\" />\n";
        $output .= is_null($this->condition) ? "" : "\t\t<![endif]-->\n";
        return $output;
    }
}
