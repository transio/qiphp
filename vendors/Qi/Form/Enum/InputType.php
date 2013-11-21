<?php
namespace Qi\Form\Enum;

/**
 * An enum of input type constants.
 */
class InputType
{
    const HIDDEN = "hidden";
    const TEXT = "text";
    const PASSWORD = "password";
    const FILE = "file";
    const CHECKBOX = "checkbox";
    const RADIO = "radio";
    const SUBMIT = "submit";
    const RESET = "reset";
    const BUTTON = "button";
    const IMAGE = "image";
    
    private function __construct() {}
}
