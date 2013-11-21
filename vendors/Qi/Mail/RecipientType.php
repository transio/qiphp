<?php
namespace Qi\Mail\Enum;

final class RecipientType
{
    const FROM = "From";
    const TO = "To";
    const CC = "Cc";
    const BCC = "Bcc";
    
    private function __construct() {}
}