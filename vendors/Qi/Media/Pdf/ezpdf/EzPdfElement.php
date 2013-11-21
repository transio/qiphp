<?php
class EzPdfElement {
	protected $pdf;
	protected $x = 0;
	protected $y = 0;
    protected $value = "";
	
	public function __construct($x, $y, $value) {
		$this->x = $x;
		$this->y = $y;
        $this->value = $value;
	}
	
	public function render(EzPdfWrapper $pdfWrapper) {
		$pdfWrapper->resetFont();
		// Must implement in extended classes
	}
}
?>