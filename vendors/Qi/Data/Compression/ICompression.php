<?php
namespace Qi\Compression;

interface Compression
{
    public static function compress($content);
    public static function decompress($content);
}