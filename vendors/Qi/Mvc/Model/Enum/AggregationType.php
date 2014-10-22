<?php
namespace Qi\Archetype\Enum;

/**
 * Aggregation Type Enum (For the Archetype class)
 * @author: smoseley
 */
final class AggregationType {
    const SINGLE     = "single";
    const RANGE      = "range";
    const COLLECTION = "collection";
    
    private function __construct() {}
}
