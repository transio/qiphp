<?php
 /**
 * QiPHP Framework
 * 
 * The Qi Html component is a powerful tool for generating HTML from 
 * Qi Templates.
 *  
 * @filesource
 * @package         qi/html
 * @version         1.1
 * @copyright       (C) 2009, Transio LLC
 * @author          Steven Moseley
 * @link            http://www.qiphp.com/
 * @license         http://www.gnu.org/licenses/gpl.html
 */

/**
 * The HtmlStylesheet class abstracts an HTML stylesheet (link) element
 */
class HtmlStylesheet {
    public $path;
    public $media;
    public $condition;
    
    public function __construct($path, array $options=array()) {
        $defaults = array(
            "media" => null,
            "condition" => null
        );
        
        $options = array_merge($defaults, $options);
        
        $this->path = $path;
        $this->media = $options["media"];
        $this->condition = $options["condition"];
    }

    public function getHtml($baseUri="/") {
        $path = preg_match("/^http:\/\/.*/", $this->path) ? $this->path : $baseUri . $this->path;
        $output = is_null($this->condition) ? "" : "\t\t<!--[if {$this->condition}]>\n";
        $output .= "\t\t<link rel=\"stylesheet\" type=\"text/css\" href=\"{$path}\" />\n";
        $output .= is_null($this->condition) ? "" : "\t\t<![endif]-->\n";
        return $output;
    }
}
