<?php
class Upc12Barcode implements Barcode {
    private $value;
    private $barColor;
    private $barPosition;

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

    public function __construct($value) {
        $this->value = $value;
    }
    
    public function generateImage($width, $height) {
    }

    /**
      * Generates and returns an SVG graphic representation of the bar code
      */
    public function generateSvg($width, $height) {
        // Round the width up to the next increment of 95 pixels (the minimum
        // incremental size of a barcode)
        if ($width % 95 != 0) $width = ceil($width/95) * 95;

        $c = 0;                     // Color Flag, alternates for each bar
        $w = round($width/95);      // Width of each of the 95 increments
        $x = 0;                     // Current bar position (x)

        // Pad the value with leading zeros to eliminate int issues
        $value = str_pad($this->value, 12, "0", STR_PAD_LEFT);

        $svg = "<"."?xml version=\"1.0\" standalone=\"no\"?".">\n";
        $svg .= "<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\"\n";
        $svg .= "\"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n";
        $svg .= "<svg width=\"{$width}\" height=\"{$height}\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\">\n";

        $svg .= $this->generateSvgSeparator("beg", $c, $x, $w, $height);
        for ($i = 0; $i < 6; $i++) {
            $num = (int)substr($value, $i, 1);
            $svg .= $this->generateSvgNumber($num, $c, $x, $w, $height);
        }
        $svg .= $this->generateSvgSeparator("mid", $c, $x, $w, $height);
        for ($i = 6; $i < 12; $i++) {
            $num = (int)substr($value, $i, 1);
            $svg .= $this->generateSvgNumber($num, $c, $x, $w, $height);
        }
        $svg .= $this->generateSvgSeparator("end", $c, $x, $w, $height);

        $svg .= "</svg>";
        return $svg;
    }

    public function generateImage($imageType, $width, $height) {
    }

    private function getNextBarColor() {
        $color = $this->colors[$this->barColor % 2];
        $this->barColor++;
        return $color;
    }

    private function getNextBarPosition() {
        $this->barColor++;
    }

    private function generateSvgSeparator($position, &$c, &$x, $w, $h) {
        foreach ($this->separators[$position] as $bw) {
            //$color = $this->getNextBarColor();
            $color = $this->colors[$c % 2];
            $bw = $w*$bw;                       // Set the bar width as [incremental width] x [number of increments]
            $svg .= "<rect width=\"{$bw}\" height=\"{$h}\" x=\"{$x}\" y=\"0\" style=\"fill:{$color};\" />\n";
            $x += $bw;                          // Increment the x position
            $c++;
        }
        return $svg;
    }

    private function generateSvgNumber($number, &$c, &$x, $w, $h) {
        if (!array_key_exists($number, $this->numbers)) {
            throw new Exception("Non-numeric value encountered for UPC-12 Barcode");
        }
        // Write the number
        $tx = round($x + $w*3.5);
        $fontSize = round($h*0.10);
        $svg .= "<text text-anchor=\"middle\" font-size=\"{$fontSize}\" x=\"{$tx}\" y=\"$h\" font-family=\"Arial\" fill=\"rgb(0,0,0)\">{$number}</text>\n";

        // Draw the bars
        foreach ($this->numbers[$number] as $bw) {
            //$color = $this->getNextBarColor();
            $color = $this->colors[$c % 2];
            $bw = $w*$bw;
            $bh = round($h*0.88);
            $svg .= "<rect width=\"{$bw}\" height=\"{$bh}\" x=\"{$x}\" y=\"0\" style=\"fill:{$color};\" />\n";
            $x += $bw;
            $c ++;
        }
        return $svg;
    }

    public static function generateCode() {
        return rand(100000000000, 999999999999);
    }
}
?>