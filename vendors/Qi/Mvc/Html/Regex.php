<?php
namespace Qi\Html;

class Regex
{
    private function __construct() {}
    
    public static function encode($value) {
        return str_replace("%", ":", urlencode($value));
    }

    public static function decode($value) {
        return urldecode(str_replace(":", "%", $value));
    }
}
