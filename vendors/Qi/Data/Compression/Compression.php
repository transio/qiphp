<?php
namespace Qi\DataSource;

class Compression
{
    const COMPRESSION_NONE = false;
    const COMPRESSION_DEFLATE = 'deflate';

    const ENCODING_NONE = false;
    const ENCODING_JSON = 'json';
    const ENCODING_SERIALIZE = 'serialize';


    public static function compress($data, $compression=self::COMPRESSION_DEFLATE, $encoding=self::ENCODING_JSON) {
        // Optional data formatting
        if (!empty($data)){
            try {
                // If encoding is on, json encode the data
                if ($encoding == self::ENCODING_JSON) {
                    $data = json_encode($data, true);
                }

                // If compression is on, inflate the data
                if ($compression == self::COMPRESSION_DEFLATE) {
                    $data = gzdeflate($data);
                }

                return $data;
            } catch (\Exception $e) {
                return null;
            }
        }
    }

    public static function decompress($data, $compression=self::COMPRESSION_DEFLATE, $encoding=self::ENCODING_JSON) {
        // Optional data formatting
        if (!empty($data)){
            try {
                // If compression is on, inflate the data
                if ($compression == self::COMPRESSION_DEFLATE) {
                    $data = gzinflate($data);
                }

                // If encoding is on, decode the data
                if ($encoding == self::ENCODING_JSON) {
                    $data = json_decode($data, true);
                }

                return $data;
            } catch (\Exception $e) {
                return null;
            }
        }
    }
}