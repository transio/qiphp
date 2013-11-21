<?php
class ChartGraphic
{    
    protected $dpi;
    protected $width;
    protected $height;
    protected $isPrint;
    protected $backgroundColor;
    protected $image;
    protected $colors = array();
    protected $fonts = array();

    public function __construct($width, $height, $rgb="FFFFFF", $dpi=96, $isPrint=false)
    {
        if ($isPrint) {
            $width = $width * $dpi;
            $height = $height * $dpi;
        }
        $this->height = $height;
        $this->width = $width;
        $this->dpi = $dpi;
        $this->isPrint = $isPrint;
        $this->image = imagecreate($width, $height);
        $this->backgroundColor = $this->allocateHexColor($rgb);
        imagefill($this->image, 0, 0, $this->backgroundColor);
    }

    public function __destruct()
    {
        imagedestroy($this->image);
    }

    public function __get($property)
    {
        switch ($property) {
            case "printWidth":
                return $this->isPrint ? round($this->width / $this->dpi, 1) : $this->width;
                break;
            case "printHeight":
                return $this->isPrint ? round($this->height / $this->dpi, 1) : $this->height;
                break;
            case "width":
                return $this->width;
                break;
            case "height":
                return $this->height;
                break;
            case "dpi":
                return $this->dpi;
                break;
            case "image":
                return $this->image;
                break;
        }
    }

    public function allocateColor($r, $g, $b)
    {
        $color = imagecolorallocate($this->image, $r, $g, $b);
        $colors[] = $color;
        return $color;
    }
    
    public function allocateHexColor($rgb)
    {
        eval("\$r = 0x" . substr($rgb, 0, 2) . ";");
        eval("\$g = 0x" . substr($rgb, 2, 2) . ";");
        eval("\$b = 0x" . substr($rgb, 4, 2) . ";");
        return $this->allocateColor($r, $g, $b);
    }

    /**
      * Sets the size of the image
      */
    public function setDimensions($width, $height, $dpi=96, $isPrint=false)
    {
        imagedestroy($this->image);
        if ($isPrint) {
            $width = $width * $dpi;
            $height = $height * $dpi;
        }
        $this->height = $height;
        $this->width = $width;
        $this->dpi = $dpi;
        $this->isPrint = $isPrint;
        $this->image = imagecreate($this->width, $this->height);
        imagefill($this->image, 0, 0, $this->backgroundColor);
    }

    public function saveImage($imageType=ChartGraphic::PNG, $folder=false)
    {
        $folder = $folder ? $folder : "/home/creports/public_html/images/reports/";
        $fileName = microtime() . rand(1000, 9999);
        $fileName = "chart" . preg_replace("([^0-9]+)", "", $fileName) . "." . $imageType;
        
        // Output the image
        switch ($imageType) {
            case MimeType::JPG:
                //header("Content-type: image/jpeg");
                imagejpeg($this->image, $folder . $fileName);
                break;
            case MimeType::GIF:
                //header("Content-type: image/gif");
                imagegif($this->image, $folder . $fileName);
                break;
            case MimeType::BMP:
                //header("Content-type: image/bmp");
                imagegif($this->image, $folder . $fileName);
                break;
            case MimeType::PNG:
            default:
                //header("Content-type: image/png");
                imagepng($this->image, $folder . $fileName);
                break;
        }
        return $fileName;
    }
    
    public function loadFonts($path)
    {
        $fonts = new DirectoryIterator($path);
        foreach ($fonts as $font) {
            $start = strrpos($font, "/");
            $length = strrpos($font, ".") - $start;
            $fontName = substr($font, $start, $length);
            $this->fonts[$fontName] = $path . "/" . $font;
        }
    }
    
    public static function getTextSize($text, $font, $size, $angle=0)
    {
        $box = @imagettfbbox($size, 0, $font, $text);
        $w = abs($box[4] - $box[0]);
        $h = abs($box[5] - $box[1]);
        return array($w, $h);
    }

    public function write($text, $font, $size, $x, $y, $color, $angle=0, $align=TextAlign::LEFT, $valign=TextAlign::TOP)
    {
        // Get the loaded font
        $font = $this->fonts[$font];

        // Adjust point size
        $size = $size * $this->dpi / 96;
        
        // Get the width and height of the text
        list($w, $h) = self::getTextSize($text, $font, $size);
        
        // Horizontally align
        switch ($align) {
            case TextAlign::LEFT:
                break;
            case TextAlign::RIGHT:
                $x -= $w * cos(deg2rad($angle));
                $y += $w * sin(deg2rad($angle));
                break;
            case TextAlign::CENTER:
                $x -= ($w/2) * cos(deg2rad($angle));
                $y += ($w/2) * sin(deg2rad($angle));
                break;
        }

        // Vertically align
        switch ($valign) {
            case TextAlign::BOTTOM:
                break;
            case TextAlign::TOP:
                $y += $h * cos(deg2rad($angle));
                $x -= $h * sin(deg2rad($angle));

                if (preg_match("([gjpqy]+)", $text) && stripos($font, "copperplate") === false) {
                    $y -= ($h/3) * cos(deg2rad($angle));
                    $x += ($h/3) * sin(deg2rad($angle));
                }
                break;
            case TextAlign::MIDDLE:
                $y += ($h/2) * cos(deg2rad($angle));
                $x += ($h/2) * sin(deg2rad($angle));
                break;
        }
        
        // Write the text
        imagettftext($this->image, $size, $angle, $x, $y, $color, $font, $text);
    }

    public function drawLine() {
    }
    
    public function drawBox() {
    }

    public function drawCircle() {
    }
}

class ChartGraphicImage extends ChartGraphic
{
    /**
      * Sets the background image
      */
    public function __construct($file, $imageType)
    {
        switch ($imageType) {
            case MimeType::GIF:
                $this->image = imagecreatefromgif($file);
                break;
            case MimeType::PNG:
                $this->image = imagecreatefrompng($file);
                break;
            case MimeType::JPG:
                $this->image = imagecreatefromjpeg($file);
                break;
            case MimeType::BMP:
                $this->image = imagecreatefromwbmp($file);
                break;
        }
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
    }
}
    
