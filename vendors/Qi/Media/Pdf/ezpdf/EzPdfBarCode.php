<?php
class EzPdfBarcode extends EzPdfElement {
    private $w = 0;
    private $h = 0;
    
    private $xPos = 0;
    private $unitWidth = 1;
    private $current = 0;
    private $fontSize = 10;
    
    private $pdfWrapper;
    
    private $numbers = array(
            0 => array(3,2,1,1),
            1 => array(2,2,2,1),
            2 => array(2,1,2,2),
            3 => array(1,4,1,1),
            4 => array(1,1,3,2),
            5 => array(1,2,3,1),
            6 => array(1,1,1,4),
            7 => array(1,3,1,2),
            8 => array(1,2,1,3),
            9 => array(3,1,1,2)
            );

    private $separators = array(
            "beg" => array(1,1,1),
            "mid" => array(1,1,1,1,1),
            "end" => array(1,1,1)
            );

    private $colors = array("rgb(0,0,0)", "rgb(255,255,255)");

    public function __construct($x, $y, $w, $h, $value) {
        // Store dimensions
        $this->x = $x;
        $this->y = $y;
        $this->w = $w;
        $this->h = $h;

        // Force 12 numbers [0-9] with leading 0's
        $value = (int) $value;
        $this->value = str_pad($value, 12, "0", STR_PAD_LEFT);  
    }
    
    public function render(EzPdfWrapper &$pdfWrapper) {
        //parent::render($pdf);
        // Store the PDF
        $this->pdfWrapper =& $pdfWrapper;
                
        // Set up starting counters
        $this->xPos = $this->pdfWrapper->getX($this->x);
        $this->current = 0; // Start at black
        $this->unitWidth = $this->w/95;
        $this->fontSize = $this->w/12.5; // Small fonts for micro barcodes
        
        // Render the barcode
        $this->renderSeparator("beg");
        for ($i = 0; $i < 12; $i++) {
            if ($i == 6) $this->renderSeparator("mid");
            $this->renderNumber($i);
        }
        $this->renderSeparator("end");
    }

    private function nextColor() {
        $c = $this->current % 2;
        $this->pdfWrapper->pdf->setColor($c,$c,$c);
        $this->current++;
    }
    
    private function renderBar($width, $height=null) {
        // Get the next color index
        $this->nextColor();
        
        // If no height specified, use total height
        if (is_null($height)) $height = $this->h;
        
        // Align bars to the top
        $y = $this->pdfWrapper->getY($this->y) + $this->h - $height;
        
        // Render the bar
        $this->pdfWrapper->pdf->filledRectangle($this->xPos, $y, $width, $height);
        
        // Move current xPos to the end of this bar
        $this->xPos += $width;
    }
    
    private function renderSeparator($position) {
        foreach ($this->separators[$position] as $barWidth) {
            $barWidth = $barWidth * $this->unitWidth;
            $this->renderBar($barWidth);
        }
    }

    private function renderNumber($i) {
        // Get the number at index i
        $number = (int) substr($this->value, $i, 1);
        
        
        // Draw the bars
        foreach ($this->numbers[$number] as $barWidth) {
            $barWidth = $this->unitWidth * $barWidth;
            $barHeight = $this->h - ($this->fontSize*1.2);
            $this->renderBar($barWidth, $barHeight);
            // $totalWidth += $barWidth;
        }
        
        // Center the text
        $txtPos = $this->xPos - ($this->fontSize*0.7);
        $this->pdfWrapper->pdf->setColor(0,0,0);
        $this->pdfWrapper->pdf->addText($txtPos, $this->pdfWrapper->getY($this->y), $this->fontSize, $number);
    }

    public static function generateCode() {
        return rand(100000000000, 999999999999);
    }
}
?>