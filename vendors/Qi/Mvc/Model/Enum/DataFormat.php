<?php
namespace Qi\Archetype\Enum;

/**
 * Data Format Enum (For the Archetype class)
 * @author: smoseley
 */
final class DataFormat {
    const ASSOC   = "assoc_array";
    const OBJECT  = "std_class_object";
    const JSON    = "json_string";
    
    private function __construct() {}
}
