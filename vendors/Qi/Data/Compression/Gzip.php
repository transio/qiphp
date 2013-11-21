<?php
namespace Qi\Compression;

class Gzip implements Compression
{
    const X_GZIP = "x-gzip";
    const GZIP = "gzip";    
    
    private function __construct() {}
    
    public static function getCompressionType()
    {
        $compressionType=false;
        if (Server::accepts(self::X_GZIP)) {
            $compressionType = self::X_GZIP;
        } else if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'],self::GZIP)!==false) {
            $compressionType=self::GZIP;
        }
    }
    
    public static function compress($content, $compressionType=null)
    {
        if (is_null($compressionType))
            $compressionType = self::getCompressionType();
            
        switch ($compressionType) {
            case self::GZIP:
            case self::X_GZIP:
                header("Content-Encoding: ".$compressionType);
                print("\x1F\x8B\x08\x00\x00\x00\x00\x00");
                $size = strlen($this->content);
                $crc = crc32($this->content);
                $this->content = gzcompress($this->content,9);
                /* strip off faulty 4 digit CRC when printing */
                return substr($this->content, 0, strlen($this->content) - 4);
                break;
            default:
                return $content;
        }
    }
}
