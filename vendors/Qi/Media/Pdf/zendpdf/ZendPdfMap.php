<?php
class ZendPdfMap {
    private $labels = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    private $params;
    private $pdf;
    public function __construct(ZendPdf &$pdf, array $params=null) {
        $this->pdf =& $pdf;
        $defaults = array(
            "zoom" => 10,
            "font-size" => 10,
            "center" => array(
                "name" => "Home",
                "address" => "3807 NE 168th Street",
                "city" => "North Miami Beach",
                "state" => "FL",
                "zip" => "33160"
            ),
            "markers" => array(
                array(
                    "name" => "Location 1 is a long name without a pipe",
                    "address" => "1111 Lincoln Road",
                    "city" => "Miami Beach",
                    "state" => "FL",
                    "zip" => "33139",
                    "email" => "test@test.com",
                    "phone" => "(123) 123-1234"
                ),
                array(
                    "name" => "Location 2 | Has a pipe yo",
                    "address" => "168 SE 1st Street, Ste 1000",
                    "city" => "Miami",
                    "state" => "FL",
                    "zip" => "33131"
                ),
            )
        );
        
        $this->params = is_array($params) ? array_merge($defaults, $params) : $defaults;
    }
    
    public function __get($key) {
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }
    
    public static function formatAddress($address) {
        $out = "";
        if (is_array($address)) {
            $fieldKeys = array("address", "city", "state", "zip");
            $data = array_intersect_key($address, array_flip($fieldKeys));
            $out = implode(",", $data);
        }
        return urlencode($out);
    }
    

    public function getMap($w,$h) {
        // Get the zip
        $zip = isset($this->center["zip"]) ? $this->center["zip"] : "00000";
        $path = $GLOBALS["settings"]->path->files . "/maps/{$zip}.png";
        
        // Check if file needs to be unset
        $fileExists = file_exists($path);
        if ($fileExists) {
            $maxAge = 1*24*60*60; // 1 days max age
            $age = mktime() - filemtime($path);
            if (true || $age > $maxAge) {
                unlink($path);
                $fileExists = false;
            }
        }
        
        // If the file doesn't exist, get it!
        if (!$fileExists) {
            // Get map center and marker
            $centerAddress = self::formatAddress($this->center);
            $markers = "&markers=color:blue|label:A|{$centerAddress}";
            
            // Add other markers
            if (is_array($this->markers) && count($this->markers)) {
                $i = 1;
                foreach($this->markers as $marker) {
                    $address = self::formatAddress($marker);
                    $label = substr($this->labels, $i, 1);
                    $markers .= "&markers=color:green|label:{$label}|{$address}";
                    $i++;
                }
            }
            
            // Generate and save the map
            $w = round($w*200/72);
            $h = round($h*200/72);
            $uri = "http://maps.google.com/maps/api/staticmap?sensor=true&maptype=roadmap&zoom={$this->zoom}&size={$w}x{$h}&center={$centerAddress}{$markers}";
            $uri = "http://maps.google.com/maps/api/staticmap?sensor=true&maptype=roadmap&size={$w}x{$h}{$markers}";
            
            copy($uri, $path);
        }
        
        // Return the path to the file
        return $path;
    }
    
    public function drawMap($x, $y, $w=540, $h=216) {
        $w = floor($w/2);
        $x = (int) $x;
        $y = (int) $y;
        $x2 = (int) $x+$w;
        $y2 = (int) $y-$h;
        
        $map = $this->getMap($w,$h);
        
        $page =& $this->pdf->getCurrentPage();
        $image = Zend_Pdf_Image::imageWithPath($map);
        $page->drawImage($image, $x, $y2, $x2, $y);
        
        // Draw Border
        $page->setLineDashingPattern(Zend_Pdf_Page::LINE_DASHING_SOLID);
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(1)); 
        $page->setLineWidth(6);
        $page->drawRoundedRectangle($x, $y2, $x2, $y, 10, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(.75)); 
        $page->setLineWidth(2);
        $page->drawRoundedRectangle($x, $y2, $x2+$w, $y, 10, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
        
        
        $this->renderAddressLabel($this->center, "A", $x+$w, $y, 0, $this->size);
        
        // Add markers
        if (is_array($this->markers) && count($this->markers)) {
            $i = 1;
            foreach($this->markers as $marker) {
                $label = substr($this->labels, $i, 1);
                $this->renderAddressLabel($marker, $label, $x+$w, $y, $i, $this->size);
                $i++;
            }
        }
    }
    
    public function renderAddressLabel($marker, $label, $x, $y, $i=0, $size=10) {
        $normal = new Zend_Pdf_Style();
        $normal->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);
        $normal->setFillColor(new Zend_Pdf_Color_Grayscale(0));
        
        $bold = clone $normal;
        $bold->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
        
        $small = clone $normal;
        $small->setFontSize(6);
        
        $y = (820-$y) + ($i*35) - ($i ? 10 : 0);
        
        $this->pdf->drawTextField($x+5, $y - 10, "{$label}", $bold);
        if (isset($marker["name"])) {
            $value = $marker["name"];
            if (isset($marker["distance"])) {
                $value .= " (" . $marker["distance"] . " mi.)";
            }
            $this->pdf->drawTextField($x+20, $y - 10, "{$value}", $bold);
            $y += 10;
        }
        if (isset($marker["address"])) {
            $this->pdf->drawTextField($x+20, $y - 10, $marker["address"], $small);
            $y += 9;
        }
        if (isset($marker["city"]) || isset($marker["state"]) || isset($marker["zip"])) {
            $value = (isset($marker["city"]) ? ($marker["city"] . ", ") : "")
                . (isset($marker["state"]) ? ($marker["state"] . " ") : "")
                . (isset($marker["zip"]) ? ($marker["zip"]) : "");
            $this->pdf->drawTextField($x+20, $y - 10, $value, $small);
        }
        
        $y -= 9;
        
        if (isset($marker["phone"])) {
            $this->pdf->drawTextField($x+145, $y - 10, "T: " . $marker["phone"], $small);
            $y += 9;
        }
        if (isset($marker["email"])) {
            $this->pdf->drawTextField($x+145, $y - 10, "E: " . $marker["email"], $small);
        }
    }
}
?>