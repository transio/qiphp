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
 * The HtmlScript class abstracts an HTML script element
 */
class HtmlScript {
    public $src;
    public $condition;
    public $extra;
    
    public function __construct($src, array $options = array()) {
        $defaults = array(
            "condition" => null,
            "extra" => ""
        );
        $options = array_merge($defaults, $options);
        
        $this->src = $src;
        $this->condition = $options["condition"];
        $this->extra = $options["extra"];
    }
    
    public function getHtml($baseUri="/") {
        $src = preg_match("/^http[s]*:\/\/.*/", $this->src) ? $this->src : $baseUri . $this->src;
        $output = is_null($this->condition) ? "" : "\t\t<!--[if {$this->condition}]>\n";
        $output .= "\t\t<script {$this->extra} type=\"text/javascript\" src=\"{$src}\"></script>\n";
        $output .= is_null($this->condition) ? "" : "\t\t<![endif]-->\n";
        return $output;
    }
}
