<?php
class EzPdfTemplate {
	public $params;
	
	public function __construct(array $params=null) {
		$defaults = array(
			"font" => "Helvetica",
			"color" => 0x000000,
			"bg" => null,
			"margin-top" => 0,
			"margin-bottom" => 0,
			"margin-left" => 0,
			"margin-right" => 0
		);
		$this->params = count($params) ? array_merge($defaults, $params) : $defaults;
	}
	
	public function __get($param) {
		return $this->get($param);
	}
	
	public function get($param) {
		return isset($this->params[$param]) ? $this->params[$param] : null;
	}
	
	public function getFont() {
		return "/usr/share/php/ezpdf/fonts/{$this->font}.afm";
	}
	
	public function getColor() {
		$color = new stdClass();
		$color->r = floor($this->color/65536)/256;
		$color->g = floor(($this->color%65536)/256)/256;
		$color->b = floor($this->color%256)/256;
		return $color;
	}
	
	public function getBg() {
		return $this->bg;
	}
}