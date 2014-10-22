<?php
namespace Qi\Form\Enum;

/**
 * An enum of form validation method constants.
 */
class Validation
{
    const AFTER     = "data-after";
    const BEFORE    = "data-before";
    const GT        = "data-gt";
    const GTE       = "data-gte";
    const LT        = "data-lt";
    const LTE       = "data-lte";
    const MAXLENGTH = "data-maxlength";
    const MINLENGTH = "data-minlength";
    
    private function __construct() {}
        
    public static function after(\DateTime $date, $message) {
        return new Validator(self::AFTER, array($date->getTimestamp()), $message);
    }
    
    public static function before(\DateTime $date, $message) {
        return new Validator(self::AFTER, array($date->getTimestamp()), $message);
    }
    
    public static function gt($value, $message) {
        return new Validator(self::AFTER, array($value), $message);
    }
    
}
