<?php
namespace Qi\Form\Enum;

/**
 * A form element validator.
 */
class Validator
{
    public function __construct($rule, array $args=array(), $message) {
        $this->_rule = $rule;
        $this->_args = $args;
        $this->_message = $message;
    }
    
    public function validate() {
        $value = $this->getInput()->getValue();
        
        switch ($this->_rule) {
            case self::AFTER:
                break;
                
            case self::BEFORE:
                break;
                
            case self::LT:
                return !empty($args) && $value < $args[0];
                
            case self::LTE:
                return !empty($args) && $value <= $args[0];

            case self::GT:
                return !empty($args) && $value > $args[0];
                
            case self::GTE:
                return !empty($args) && $value >= $args[0];
        }
    }
    
    public function getInput() {
    }
    
    
}
