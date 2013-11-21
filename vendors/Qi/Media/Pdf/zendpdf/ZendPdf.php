<?php
// Suppress all messages so as not to damage the PDF
ini_set('display_errors', 0);
error_reporting(E_ERROR);

require_once 'Zend/Pdf.php';

class ZendPdf {
	const CURSOR_MODE = 0;
	const OVERLAY_MODE = 1;
	
	const TEXTFIELD = "TextField";
	const TEXTAREA = "TextArea";
	const CHECKBOX = "Checkbox";
	const BBCODE = "BBCode";
	Const BARCODE = "BarCode";
	Const SIGNATURE = "Signature";
	const MAP = "Map";
	
	private $params;
	private $pages;
	private $template;
	private $pdf;
	
	private $width;
	private $height;
	
	private $currentIndex;
	private $currentPage;
	private $currentY;
	
	
	public function __construct(array $params=null) {
		$defaults = array(
			"mode" => self::CURSOR_MODE,
			"dpi" => 72,
			"points" => 72,
			"xOffset" => 0,
			"yOffset" => 0,
		);
		
		// Set parameters
		$this->params = count($params) ? array_merge($defaults, $params) : $defaults;
		
		// Set other vars
		$this->template = isset($params["template"]) ? $params["template"] : new ZendPdfTemplate();
		$this->height = $this->template->getHeight();
		$this->width = $this->template->getWidth();
		
		$this->pages = array();
		
		// Current Page & Cursor Position
		$this->newPage($params);
	}
	
	public function __get($param) {
		$param = str_replace("_", "-", $param);
		return $this->get($param);
	}
	
	public function get($param) {
		return isset($this->params[$param]) ? $this->params[$param] : null;
	}
	
	public function getX($x) {
		return $this->pixelsToPoints($x) + $this->xOffset;
	}
	
	public function getY($y) {
		return $this->height - $this->pixelsToPoints($y) + $this->yOffset;
	}
	
	public function setDy($y) {
		$this->currentY += $y;
	}
	
	public function &getTemplate() {
		if (is_null($this->template)) $this->template = new ZendPdfTemplate();
		return $this->template;
	}
	
	public function setTemplate(ZendPdfTemplate $template) {
		$this->template = $template;
	}
	
	public function getFillColor() {
		return $this->currentPage->getFillColor();
	}
	public function setFillColor($color) {
		$this->fill_color = $color instanceof Zend_Pdf_Color ? $color : ZendPdfTemplate::parseColor($color);
		return $this->currentPage->setFillColor($this->fill_color);
	}
	
	public function resetStyle() {
		$this->currentPage->setStyle($this->template->getStyle());
	}
	
	public function &getCurrentPage() {
		return $this->currentPage;
	}
	
	public function setCurrentPage($index) {
		$this->currentIndex = $index;
		$this->currentPage =& $this->pages[$this->currentIndex];
	}
	
	public function &addPage(ZendPdfTemplate $template, array $params=null) {
		$defaults = array(
			"before" => null,	// Insert page before before an existing page?
			"toc" => true,		// Render this page in the table of contents?
			"number" => true,	// Render page numbers on this page?
		);
		
		$params = empty($params) ? $defaults : array_merge($defaults, $params);
		
		// Find insert position
		$before = $params["before"];
		
		
		// Set up the page
		$page = new stdClass();
		$page->page = $template->getPage();
		$page->toc = $params["toc"];
		$page->number = $params["number"];
		
		
		if ($before === null || $before < 0 || $before >= count($this->pages)) {
			$this->pages[] = $page;
			$this->currentIndex = count($this->pages) - 1;
		} else {
			$before = (int) $before;
			if ($before == 0) {
				$this->pages = array_merge( array(), array($page), $this->pages );
				$this->currentIndex = 0;
			} else {
				$this->pages = array_merge( array_slice($this->pages, 0, $before), array($page), array_slice($this->pages, $before) );
				$this->currentIndex = $before;
			}
		}
		$this->currentPage =& $this->pages[$this->currentIndex]->page;
		$this->currentY = $this->height - $template->margin_top;
		
		// Required?
		$this->template =& $template;
		$this->resetStyle();
		
		return $this->currentPage;
	}
	
	public function &newPage(array $params=null) {
		// New Page
		$page =& $this->addPage($this->template, $params);
		$page->setStyle($this->template->getStyle());
		$this->currentY = $this->height - $this->template->margin_top;
		return $page;
	}
	
	public function &addTocPage(ZendPdfTemplate $template) {
		$cover = $this->addPage($template, array("before" => 0, "toc" => false, "number" => false));
		
		$this->drawBBCode("[H1]Table of Contents");
		foreach ($this->pages as $i => $page) {
			if (isset($page->tocs) && !empty($page->tocs)) {
				foreach ($page->tocs as $toc) {
					$params = $template->bbcode["[P]"];
					$params["margin-bottom"] = 4;
					switch ($toc->order) {
						case 1:
							$params["font-face"] = Zend_Pdf_Font::FONT_HELVETICA_BOLD;
							$params["margin-left"] = 0;
							break;
						case 2:
						default:
							$params["font-face"] = Zend_Pdf_Font::FONT_HELVETICA;
							$params["margin-left"] = 10;
							break;
					}
					// Set up font
					$font = Zend_Pdf_Font::fontWithName($params["font-face"]);
					$size = $params["font-size"];
					$this->currentPage->setFont($font, $size);
					
					$num = $i + 1;
					$numWidth = self::calculateWidth($num, $font, $size);
					$textWidth = self::calculateWidth($toc->text, $font, $size);
					$left = $params["margin-left"]+$this->template->margin_left;
					$right = $this->template->getWidth() - $this->template->margin_right;
					
					$this->currentPage->drawText($toc->text, $left, $this->currentY);
					$this->currentPage->drawText($num, $right - $numWidth, $this->currentY);
					
					$this->currentPage->setLineColor(new Zend_Pdf_Color_GrayScale(0.2)); 
					$this->currentPage->setLineDashingPattern(array(1, 3), 1.6); 
					$this->currentPage->drawLine(round(($right - $numWidth - 10)/4)*4, $this->currentY,$left + $textWidth + 10, $this->currentY);
					$this->currentY -= ($params["font-size"] + $params["margin-bottom"]);
				}
			}
		}
		return $cover;
	}
	
	public function addTocHeader($order, $text) {
		$page =& $this->pages[$this->currentIndex];
		if ($page->toc) {
			if (!isset($page->tocs) || !is_array($page->tocs)) {
				$page->tocs = array();
			}
			$toc = new stdClass();
			$toc->order = $order;
			$toc->text = $text;
			$page->tocs[] = $toc;
		}
	}
	
	
	public function pixelsToPoints($val) {
		return round($val * $this->points / $this->dpi);
	}
	
	public function drawPageNumber($number) {
		$text = "page {$number}";
		$style = $this->template->getStyle();
		$style->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 9.2);
		$width = self::calculateWidth($text, $style->getFont(), $style->getFontSize());		
		$this->currentPage->setStyle($style);
		$this->currentPage->drawText($text, $this->template->getWidth() - 24 - $width, 18);
	}
	
	public function drawFields(array $fields) {
		foreach ($fields as $field) {
			if (count($field) < 4) continue;
			switch ($field[0]) {
				case self::TEXTFIELD:
					if (isset($field[4]))
						$this->drawTextField($field[1], $field[2], $field[3], $field[4]);
					else
						$this->drawTextField($field[1], $field[2], $field[3]);
					break;
				case self::TEXTAREA:
					if (isset($field[7]))
						$this->drawTextArea($field[1], $field[2], $field[3], $field[4], $field[5], $field[6], $field[7]);
					else
						$this->drawTextArea($field[1], $field[2], $field[3], $field[4], $field[5], $field[6]);
					break;
				case self::CHECKBOX:
					break;
				case self::BARCODE:
					if (count($field) >= 6)
						$this->drawBarCode($field[1], $field[2], $field[3], $field[4], $field[5]);
					break;
				case self::BBCODE:
					break;
				case self::SIGNATURE:
					break;
				case self::MAP:
					if (count($field) >= 5)
						$this->drawMap($field[1], $field[2], $field[3], $field[4], isset($field[5]) ? $field[5] : null);
					break;
			}
		}
	}
	
	public function drawCheckbox($x, $y, $value, $condition, Zend_Pdf_Style $style=null) {
		if ($value == $condition) {
			if (isset($style)) $this->currentPage->setStyle($style);
			$this->currentPage->drawText("X", $this->getX($x), $this->getY($y));
		}
	}
	
	public function drawTextField($x, $y, $value, Zend_Pdf_Style $style=null) {
		if (isset($style)) {
			$oStyle = $this->currentPage->getStyle();
			$this->currentPage->setStyle($style);
		}
		$this->currentPage->drawText($value, $this->getX($x), $this->getY($y));
		if (isset($oStyle)) {
			$this->currentPage->setStyle($oStyle);
		}
	}
	
	public function drawTextArea($x1, $y1, $x2, $y2, $lines, $value, Zend_Pdf_Style $style=null) {
		$x1 = $this->getX($x1);
		$y1 = $this->getY($y1);
		$x2 = $this->getX($x2);
		$y2 = $this->getY($y2);
		
		$chars = round(($x2-$x1)/5);
		$lineHeight = $lines > 1 ? ($y2-$y1)/($lines-1) : 0;
		$text = preg_replace('/[\r\n]+/', ' ', $value);
		$text = wordwrap($text, $chars, '\n');
		$text = explode('\n', $text);
		
		if (isset($style)) {
			$oStyle = $this->currentPage->getStyle();
			$this->currentPage->setStyle($style);
		}
		
		for ($i = 0; $i < $lines; $i++) {
			if (array_key_exists($i, $text)) {
				$y = $y1 + ($lineHeight*$i);
				$this->currentPage->drawText($text[$i], $x1, $y);
			}
		}
		if (isset($oStyle)) {
			$this->currentPage->setStyle($oStyle);
		}
	}
	
	public function drawMap($x, $y, $w, $h, $params=null) {
		$map = new ZendPdfMap($this, $params ? $params : array());
		$map->drawMap($this->getX($x), $this->getY($y), $w, $h);
	}
	
	public function drawImage($path, $x, $y, $w, $h) {
		$image = Zend_Pdf_Image::imageWithPath($path);
		$this->currentPage->drawImage($image, (int) $x, (int) $y, (int) $x+$w, (int) $y+$h);
	}
	
	public function drawBarcode($x, $y, $w, $h, $code, Zend_Pdf_Style $style=null) {
		$barCode = new ZendPdfBarcode($this);
		$barCode->drawBarCode($this->getX($x), $this->getY($y), $w, $h, $code);
	}
	
	public function drawBBCode($text, array $bbcode=null) {
		$bbcode = count($bbcode) ? array_merge($this->template->bbcode, $bbcode) : $this->template->bbcode;
		
		// Strip whitespace
		$text = preg_replace('/[\s\r\n\t]+/', ' ', $text);
		$lines = preg_split('/(\[[A-Z1-5\*\/]*\])/', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		
		for ($i=0; $i<count($lines); $i=$i+2) {
			if (isset($lines[$i]) && isset($lines[$i+1])) {
				$tag = $lines[$i];
				$text = $lines[$i+1];
				switch ($tag) {
					case "[H1]":
						$this->addTocHeader(1, $text);
						break;
					case "[H2]":
						$this->addTocHeader(2, $text);
						break;
				}
				switch ($tag) {
					case "[H1]":
					case "[H2]":
					case "[H3]":
					case "[H4]":
					case "[H5]":
					case "[P]":
					case "[*]":
						$params = $bbcode[$tag];
						$params["bullet"] = $this->bullet;
						$this->drawPageWrappedText($text, $params);
						if (is_numeric($this->bullet)) $this->bullet++;
						break;
					case "[LIST]":
					case "[LIST1]":
						// Start of list margins
						$params = $bbcode[$tag];
						if (isset($params["margin-top"])) $this->setDy(-$params["margin-top"]);
						if (isset($params["bullet"])) $this->bullet = $params["bullet"];
						break;
					case "[/LIST]":
					case "[/LIST1]":
						// End of list margins
						$token = "[".strtoupper(substr($tag, 2));
						$params = $bbcode[$token];
						if (isset($params["margin-bottom"])) $this->setDy(-$params["margin-bottom"]);
						$this->bullet = null;
						break;
					case "[NEWPAGE]":
						$this->newPage();
						break;
					default:
						$params = $bbcode["[P]"];
						if (count($line) == 1) {
							$this->drawPageWrappedText($line[0], $params);
						} else {
							$this->pdf->drawPageWrappedText(trim($line[0])." ".trim($text), $params);
						}
						break;
				}
			}
		}
		$this->bullet = null;
	}
	
	public function drawPageWrappedText($text, array $params=null) {
		$defaults = array(
			"display" => "block",
			"font-size" => 10, 
			"font-face" => null,
			"line-height" => 12, 
			"fill-color" => 0x000000, 
			"margin-top" => 0, 
			"margin-bottom" => 0,
			"margin-left" => 0,
			"margin-right" => 0,
			"text-align" => "left",
			"bullet" => ""
		);
		$params = empty($params) ? $defaults : array_merge($defaults, $params);
		
		$style = $this->template->getStyle();
		if (isset($params["font-face"])) {
			$style->setFont(Zend_Pdf_Font::fontWithName($params["font-face"]), $params["font-size"]);
		} else {
			$style->setFontSize($params["font-size"]);
		}
		$style->setFillColor(ZendPdfTemplate::parseColor($params["fill-color"]));
		$lineHeight = $params["line-height"] ? $params["line-height"] : 1.2*$params["font-size"];
		
		$w = $this->width - $this->template->margin_left - $this->template->margin_right - $params["margin-left"] - $params["margin-right"];
		$lines = self::getWrappedText($text, $style, $w);
		
		$this->currentPage->setStyle($style);
		foreach($lines as $line) {
			//$line = "Y:{$this->currentY}|H:{$this->height}|M:{$this->template->margin_top}:{$this->template->margin_bottom} - {$line}";
			$xPos = $params["text-align"] == "right"
				? $this->width - $line["width"] - $this->template->margin_right - $params["margin-right"]
				: $xPos = $this->template->margin_left + $params["margin-left"];
			$yPos = $this->currentY - $params["margin-top"];
			
			switch ($params["display"]) {
				case "list":
					$delim = "";
					if ($params["bullet"] === "*") {
						$delim = "•";
					} else {
						$delim = "{$params['bullet']}.";
					}
					$this->currentPage->drawText($delim, $xPos-24, $yPos);
					break;
				case "inline":
					break;
				case "block":
				default:
					break;
			}
			
			$this->currentPage->drawText($line["text"], $xPos, $yPos);
			if ($params["display"] != "inline") $this->currentY -= $lineHeight;
			if ($this->currentY < $this->template->margin_bottom) $this->newPage();
		}
		$this->currentPage->setStyle($this->template->getStyle());
		
		// Move cursor
		if ($params["display"] != "inline") $this->currentY -= ($params["margin-bottom"] + $params["margin-top"]);
	}
	
	public function drawWrappedText($text, $x, $y, $w, Zend_Pdf_Style $style=null, $lineHeight=null) {
		if (is_null($style)) $style = $this->template->getStyle();
		if (is_null($lineHeight)) $lineHeight = 1.2 * $style->getFontSize();
		$lines = self::getWrappedText($text, $style, $w);
		$this->currentPage->setStyle($style);
		$currentY = 0;
		foreach($lines as $line) {
			$this->currentPage->drawText($line["text"], $this->template->margin_left + $x, $currentY + $y);
			$currentY -= $lineHeight;
		}
		$this->currentPage->setStyle($this->template->getStyle());
	}
	
	public static function getWrappedText($text, Zend_Pdf_Style $style, $width) {
		$font = $style->getFont();
		$size = $style->getFontSize();
		
		// Strip out whitespace
		$text = preg_replace('/[\s\r\n\t]+/', ' ', $text);
		
		// Set up lines
		$lines = array();
		$spaceWidth = self::calculateWidth(" ", $font, $size);
		
		// Split the words into lines
		$words = explode(" ", $text);
		foreach ($words as $word) {
			$wordWidth = self::calculateWidth($word, $font, $size);
			if(count($lines) > 0 && $lines[count($lines)-1]["width"] + $wordWidth < $width) {
				$lines[count($lines)-1]["text"] .= " " . $word;
				$lines[count($lines)-1]["width"] += $spaceWidth + $wordWidth;
			} else {
				$lines[] = array("text" => $word, "width" => $wordWidth);
			}
		}
		return $lines;
	}
	
	public static function calculateWidth($text, $font, $size) {
		$str = iconv('UTF-8', 'UTF-16BE//IGNORE', $text);
		$characters = array();
		for ($i = 0; $i < strlen($str); $i++) {
			$characters[] = (ord($str[$i++]) << 8 ) | ord($str[$i]);
		}
		$glyphs = $font->glyphNumbersForCharacters($characters);
		$widths = $font->widthsForGlyphs($glyphs);
		return array_sum($widths) * $size / $font->getUnitsPerEm();
	}
	
	// TODO - Drawmode?
	public function setDrawMode($drawMode=self::CURSOR_MODE) {
	}
	
	
	//public function &generatePdf() {
	public function render($fileName=null, $attached=false) {
		// Set up the EZ PDF Object
		$this->pdf = new Zend_Pdf();
		$number = 0;
		foreach ($this->pages as $page) {
			$number++;
			if (isset($page->number) && $page->number === true) {
				$this->currentPage =& $page->page;
				$this->drawPageNumber($number);
			}
			$this->pdf->pages[] =& $page->page;
		}
		$output = $this->pdf->render();
		
		if ($attached) {
			$attach = "Content-Disposition: attachment";
			if (!is_null($fileName))
				$attach .= "; filename=\"{$fileName}\"";
			header($attach);
		}

		header("Content-Type: application/pdf");
		header("Pragma: private");
		header("Cache-Control: private");
		header("Content-Length: " . strlen($output));
		//header("Cache-Control: max-age=290304000, public");
		//header("Expires: " . date("D, d M Y H:i:s", time() + 290304000) . " GMT");

		print($output);
		exit();
	}
	
}
?>