<?php
namespace Qi\Form\Enum;

/**
 * An enum of list type constants.
 */
class ListType
{
    const SELECT = "select";
    const RADIO = "radiolist";
    const CHECKBOX = "checklist";
    const RATING = "ratinglist";
    
    private function __construct() {}
}
