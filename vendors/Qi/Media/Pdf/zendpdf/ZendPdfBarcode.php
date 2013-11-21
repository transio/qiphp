<?php
require_once 'Zend/Pdf.php';

class ZendPdfBarcode {
	private $w = 0;
	private $h = 0;
	
	private $xPos = 0;
	private $unitWidth = 1;
	private $current = 0;
	private $font = null;
	private $fontSize = 10;
	
	private $pdf;
	
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

    public function __construct(ZendPdf $pdf) {
		$this->font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
		$this->pdf =& $pdf;
    }
	
	public function drawBarcode($x, $y, $w, $h, $value) {
		// Store the Dimensions
		$this->x = $x+4;
		$this->y = $y+4;
		$this->w = $w-8;
		$this->h = $h-8;
		
		
		$this->pdf->getCurrentPage()->setFillColor(new Zend_Pdf_Color_Grayscale(1)); 
		$this->pdf->getCurrentPage()->drawRectangle($x, $y, $x+$w, $y+$h, Zend_Pdf_Page::SHAPE_DRAW_FILL);
		
		// Force 12 numbers [0-9] with leading 0's
		$value = (int) $value;
        $this->value = str_pad($value, 12, "0", STR_PAD_LEFT);  
		
		// Set up starting counters
		$this->xPos = $this->x;
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
		$this->pdf->getCurrentPage()->setFillColor(new Zend_Pdf_Color_Grayscale($c)); 
		$this->current++;
	}
	
	private function renderBar($width, $height=null) {
		// Get the next color index
		$this->nextColor();
		
		// If no height specified, use total height
		if (is_null($height)) $height = $this->h;
		
		// Align bars to the top
		$y = $this->y + $this->h;
		
		// Render the bar
		$this->pdf->getCurrentPage()->drawRectangle($this->xPos, $y, $this->xPos + $width, $y - $height, Zend_Pdf_Page::SHAPE_DRAW_FILL);
		
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
        $this->pdf->getCurrentPage()->setFillColor(new Zend_Pdf_Color_Grayscale(0.2)); 
		$this->pdf->getCurrentPage()->setFont($this->font, $this->fontSize);
		$this->pdf->getCurrentPage()->drawText($number, $txtPos, $this->y);
    }

    public static function generateCode() {
        return rand(100000000000, 999999999999);
    }
}
?>