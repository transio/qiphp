<?php
namespace Qi\Token;

/**
 * QiPHP Framework
 */
class Token
{    
    private $_value;
    private $_case;
    private $_number;
    
    /**
     * constructor
     */
    public function __construct($value, $case=TokenCase::UNDERSCORE, $number=TokenNumber::SINGULAR)
    {
        $this->_value = $value;
        $this->_case = $case;
        $this->_number = $number;
    }
    
    /**
     * Override default __toString functionality
     * @return String value of token
     */
    public function __toString()
    {
        return $this->_value;
    }
    
    public function toTitle()
    {
        return new Token(TokenCase::convert($this->_value, $this->_case, TokenCase::TITLE), TokenCase::TITLE, $this->_number);
    }
    
    public function toCamel()
    {
        return new Token(TokenCase::convert($this->_value, $this->_case, TokenCase::CAMEL), TokenCase::CAMEL, $this->_number);
    }
    
    public function toHyphen()
    {
        return new Token(TokenCase::convert($this->_value, $this->_case, TokenCase::HYPHEN), TokenCase::HYPHEN, $this->_number);
    }
    
    public function toUnderscore()
    {
        return new Token(TokenCase::convert($this->_value, $this->_case, TokenCase::UNDERSCORE), TokenCase::UNDERSCORE, $this->_number);
    }
    
    public function toConstant()
    {
        return new Token(TokenCase::convert($this->_value, $this->_case, TokenCase::CONSTANT), TokenCase::CONSTANT, $this->_number);
    }
    
    public function toSingular()
    {
        // TODO - Implement This
        return $this;
    }
    
    public function toPlural()
    {
        // TODO - Implement This
        return $this;
    }
}

