<?php
namespace Qi\Http\Enum;

/**
 * An enum of http request method constants.
 */
final class Method {
    const POST = "POST";
    const GET = "GET";
    const PUT = "PUT";
    const PATCH = "PATCH";
    const DELETE = "DELETE";
    const HEAD = "HEAD";
    
    private function __construct() {}
}