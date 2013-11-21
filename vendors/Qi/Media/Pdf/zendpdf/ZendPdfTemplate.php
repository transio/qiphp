<?php
class ZendPdfTemplate {
	public $params;
	private $style;
	
	public function __construct(array $params=null) {
		$defaults = array(
			"margin-top" => 0,
			"margin-bottom" => 0,
			"margin-left" => 0,
			"margin-right" => 0,
			
			"font-face" => Zend_Pdf_Font::FONT_HELVETICA,
			"font-size" => 10,
			"fill-color" => 0x000000,
			"line-color" => null,
			"line-width" => null,
			"line-dashing-pattern" => null,
			"line-dashing-phase" => null,
			
			"bbcode" => array(
				"[H1]" => array("font-size" => 16, "line-height" => 18, "color" => 0x000000, "margin-top" => 6, "margin-bottom" => 4, "font-face" => Zend_Pdf_Font::FONT_HELVETICA_BOLD),
				"[H2]" => array("font-size" => 14, "line-height" => 16, "color" => 0x000000, "margin-top" => 6, "margin-bottom" => 4, "font-face" => Zend_Pdf_Font::FONT_HELVETICA_BOLD),
				"[H3]" => array("font-size" => 12, "line-height" => 14, "color" => 0x000000, "margin-top" => 6, "margin-bottom" => 4, "font-face" => Zend_Pdf_Font::FONT_HELVETICA_BOLD),
				"[H4]" => array("font-size" => 10, "line-height" => 12, "color" => 0x000000, "margin-top" => 6, "margin-bottom" => 4, "font-face" => Zend_Pdf_Font::FONT_HELVETICA_BOLD),
				"[P]" => array("font-size" => 10, "line-height" => 12, "color" => 0x000000, "margin-bottom" => 10, "font-face" => Zend_Pdf_Font::FONT_HELVETICA),
				"[LIST]" => array("margin-bottom" => 8, "bullet" => "*"),
				"[LIST1]" => array("margin-bottom" => 8, "bullet" => 1),
				"[*]" => array("type" => "list", "font-size" => 10, "line-height" => 12, "color" => 0x000000, "margin-bottom" => 2, "margin-left" => 32, "font-face" => Zend_Pdf_Font::FONT_HELVETICA)
			)
		);
		
		
		$this->params = count($params) ? array_merge($defaults, $params) : $defaults;
		
		if (isset($params["page"])) {
			$this->setPage($params["page"]);
		}
		
		// Now set up private template params
		$font = Zend_Pdf_Font::fontWithName($this->font_face);
		$this->style = new Zend_Pdf_Style();
		$this->style->setFont($font, $this->font_size);
		//$this->style->setFontSize();
		$this->setFillColor($this->fill_color);
		if ($this->line_color !== null) 
			$this->setLineColor($this->line_color);
		if ($this->line_dashing_pattern !== null) 
			$this->style->setLineDashingPattern($this->line_dashing_pattern);
		if ($this->line_dashing_phase !== null) 
			$this->style->setLineDashingPhase($this->line_dashing_phase);
		if ($this->line_width !== null) 
			$this->style->setLineWidth($this->line_width);
	}
	
	// Param Getters
	public function __get($param) {
		$param = str_replace("_", "-", $param);
		return $this->get($param);
	}
	public function get($param) {
		return isset($this->params[$param]) ? $this->params[$param] : null;
	}
	
	public function getParams() {
		return $this->params;
	}
	
	// Page
	public function getPage() {
		if (!($this->page instanceof Zend_Pdf_Page)) $this->page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_LETTER);
		$this->page->setStyle($this->getStyle());
		return clone $this->page;
	}
	public function setPage($page) {
		if (!$page instanceof Zend_Pdf_Page) {
			try {
				if (!file_exists($page)) throw new Exception();
				$p = Zend_Pdf::load($page);
				if (!isset($p->pages) || !count($p->pages)) throw new Exception();
				$page = clone $p->pages[0];
			} catch (Exception $e) {
				$page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_LETTER);
			}
		}
		$this->page = $page;
	}
	
	// Width / Height Getters
	public function getWidth() {
		return $this->page->getWidth();
	}
	public function getHeight() {
		return $this->page->getHeight();
	}
	
	// Style
	public function getStyle() {
		return clone $this->style;
	}
	
	public function setStyle(Zend_Pdf_Style $style) {
		return $this->style = $style;
	}
	
	// Fill Color
	public function getFillColor() {
		return $this->style->getFillColor();
	}
	public function setFillColor($color) {
		$this->fill_color = $color instanceof Zend_Pdf_Color ? $color : self::parseColor($color);
		return $this->style->setFillColor($this->fill_color);
	}
	
	// Line Color
	public function getLineColor() {
		return $this->style->setLineColor($color);
	}
	public function setLineColor($color) {
		$this->line_color = $color instanceof Zend_Pdf_Color ? $color : self::parseColor($color);
		return $this->style->setLineColor($this->line_color);
	}
	
	// Line Dashing Pattern
    public function getLineDashingPattern() {
		return $this->style->setLineDashingPattern($pattern, $thickness);
	}
    public function setLineDashingPattern($pattern, $thickness) {
		return $this->style->setLineDashingPattern($pattern, $thickness);
	}
	
	// Font
	public function getFont() {
		return $this->style->getFont();
	}
	public function setFont($font=null, $size=null) {
		$this->style->setFont($font, $size === null ? $this->style->getFontSize() : $size);
	}
	
	// Font Face
	public function getFontFace() {
		return $this->font_face;
	}
	public function setFontFace($face) {
		$this->font_face = $face;
		$this->setFont(Zend_Pdf_Font::fontWithName($face));
	}
	
	// Font Size
	public function getFontSize() {
		return $this->style->getFontSize();
	}
	public function setFontSize($size) {
		$this->style->setFontSize($size);
	}
	
	
	// Color Helpers
	public static function parseColor($hex=0x000000) {
		$color = is_null($hex) ? $this->color : $hex;
		return new Zend_Pdf_Color_Rgb(floor($color/65536)/256, floor(($color%65536)/256)/256, floor($color%256)/256);
	}
	
}	