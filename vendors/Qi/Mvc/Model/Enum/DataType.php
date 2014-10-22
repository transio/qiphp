<?php
namespace Qi\Archetype\Enum;

/**
 * Data Type Enum (For the Archetype class)
 * @author: smoseley
 */
final class DataType {
    const INTEGER   = "int";
    const STRING    = "string";
    const DECIMAL   = "double";
    const BOOLEAN   = "boolean";
    const DATETIME  = "DateTime";
    const DATE  = "Date";
    const TIME  = "Time";
    
    private function __construct() {}
}
