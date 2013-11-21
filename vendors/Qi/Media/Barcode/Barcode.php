<?php
interface Barcode {
    public function __construct($value);
    public function generateSvg($width, $height);
    public function generateImage($imageType, $width, $height);
}
    
