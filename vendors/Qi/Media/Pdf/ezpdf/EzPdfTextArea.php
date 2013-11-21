<?php
class EzPdfTextArea extends EzPdfElement {
	private $x2=100;
	private $y2=100;
	private $lines=1;
	
	public function __construct($x1, $y1, $x2, $y2, $lines, $value) {
		parent::__construct($x1, $y1, $value);
		$this->x2 = $x2;
		$this->y2 = $y2;
        $this->lines = $lines;
	}
	
	public function render(EzPdfWrapper &$pdfWrapper) {
		parent::render($pdfWrapper);
		$x1 = $pdfWrapper->getX($this->x);
		$y1 = $pdfWrapper->getY($this->y);
		$x2 = $pdfWrapper->getX($this->x2);
		$y2 = $pdfWrapper->getY($this->y2);
		
		$chars = round(($x2-$x1)/5);
		$lineHeight = ($y2-$y1)/($this->lines-1);
		$text = preg_replace('/[\r\n]+/', ' ', $this->value);
		$text = wordwrap($text, $chars, '\n');
		$text = explode('\n', $text);

		for ($i = 0; $i < $this->lines; $i++) {
			if (array_key_exists($i, $text)) {
				$y = $y1 + ($lineHeight*$i);
				$pdfWrapper->pdf->addText($x1, $y, 10, $text[$i]);
			}
		}
	}
}
?>
