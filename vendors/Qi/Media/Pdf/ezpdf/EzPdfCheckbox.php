<?php
class EzPdfCheckbox extends EzPdfElement {
    private $condition = "";
    
    public function __construct($x, $y, $value, $condition) {
        parent::__construct($x, $y, $value);
        $this->condition = $condition;
    }
    
    public function render(EzPdfWrapper &$pdfWrapper) {
        parent::render($pdfWrapper);
        if ($this->value == $this->condition) {
            $pdfWrapper->pdf->addText($pdfWrapper->getX($this->x), $pdfWrapper->getY($this->y), 10, "X");
        }
    }
}
?>