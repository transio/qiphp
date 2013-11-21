<?php
class EzPdfSignature extends EzPdfElement {
	public function __construct($x, $y, $signatureFile) {
		parent::__construct($x, $y, $signatureFile);
	}
	
	public function render(EzPdfWrapper &$pdfWrapper) {
		parent::render($pdfWrapper);
	}
}
?>