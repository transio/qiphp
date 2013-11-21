<?php
namespace Qi\Form\Enum;

/**
 * An enum of input format constants.
 */
class InputFormat
{
    const EMAIL = "email";
    const NUMERIC = "numeric";
    const INTEGER = "int";
    const PERCENTAGE = "percentage";
    const CCDATE = "ccdate";
    const CCNUMBER = "ccnumber";
    const DOB = "dob";
    const SSN = "ssn";
    const PHONE = "phone";
    
    private function __construct() {}
}
