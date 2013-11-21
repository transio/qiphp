<?php
class EzPdfTextField extends EzPdfElement {
	private $pointSize;
	
	public function __construct($x, $y, $value, $pointSize = 10) {
		parent::__construct($x, $y, $value);
		$this->pointSize = $pointSize;
	}
	
	public function render(EzPdfWrapper &$pdfWrapper) {
		parent::render($pdfWrapper);
		$pdfWrapper->pdf->addText($pdfWrapper->getX($this->x), $pdfWrapper->getY($this->y), $this->pointSize, $this->value);
	}
}
?>