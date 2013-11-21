<?php
namespace Qi\Form\Enum;

/**
 * An enum of form validation method constants.
 */
class ValidationType
{
    const BROWSER = "browser";
    const SERVER = "server";
    const AJAX = "ajax";
    
    private function __construct() {}
}
