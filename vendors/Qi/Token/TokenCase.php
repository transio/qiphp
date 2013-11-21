<?php
namespace Qi\Token;

/**
 * TokenCase enum
 */
class TokenCase
{
    const CAMEL = "CAMEL";
    const TITLE = "TITLE";
    const HYPHEN = "HYPHEN";
    const UNDERSCORE = "UNDERSCORE";
    const CONSTANT = "CONSTANT";
    const NAME = "NAME";
    
    private function __construct() {}
    
    /**
     * Convert a token from one case to another
     * @return String the converted name
     * @param $value String the name to be converted
     * @param $fromCase String the case to be converted from (default autodetect)
     */
    public static function convert($value, $fromCase, $toCase)
    {
        $pieces = self::split($value, $fromCase);
        switch ($toCase) {
            case self::TITLE:
                $value = "";
                foreach ($pieces as $piece) {
                    $value .= strtoupper(substr($piece, 0, 1)) . strtolower(substr($piece, 1));
                }
                break;
            case self::CAMEL:
                $value = "";
                $first = true;
                foreach ($pieces as $piece) {
                    if ($first) {
                        $value .= strtolower($piece);
                        $first = false;
                    } else {
                        $value .= strtoupper(substr($piece, 0, 1)) . strtolower(substr($piece, 1));
                    }
                }
                break;
            case self::HYPHEN:
                $value = strtolower(implode("-", $pieces));
                break;
            case self::UNDERSCORE:
                $value = strtolower(implode("_", $pieces));
                break;
            case self::CONSTANT:
                $value = strtoupper(implode("_", $pieces));
                break;
            case self::NAME:
                $newPieces = array();
                foreach ($pieces as $piece) {
                    array_push($newPieces, strtoupper(substr($piece, 0, 1)) . strtolower(substr($piece, 1)));
                }
                $value = implode(" ", $newPieces);
                break;
            default:
                break;
        }
        return $value;
    }
    
    public static function split($value, $fromCase, $splitter="[[SPLITTER]]")
    {
        $pieces = array();
        switch ($fromCase) {
            case self::TITLE:
                $value = ereg_replace("([A-Z]+)", "{$splitter}\\1", $value);
                $value = substr($value, strlen($splitter));
                $pieces = explode($splitter, $value);
                break;
            case self::CAMEL:
                $value = ereg_replace("([A-Z]+)", "{$splitter}\\1", $value);
                $pieces = explode($splitter, $value);
                break;
            case self::HYPHEN:
                $pieces = explode("-", $value);
                break;
            case self::UNDERSCORE:
            case self::CONSTANT:
                $pieces = explode("_", $value);
                break;
            case self::NAME:
                $pieces = explode(" ", $value);
                break;
            default:
                $pieces = array($value);
                break;
        }
        return $pieces;
    }
}
