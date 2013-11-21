<?php
namespace Flipkey\DataSource\NoSql;

use \Flipkey\DataSource\Compression;
use \Flipkey\DataSource\Encoding;

// TODO - SGM - Make a FileCache NoSql class

/**
 * FileCache connection class
 */
class FileCache
    extends AbstractConnection
    implements Connection
{
    /**
     * @param String $key - The cache key
     * @return 
     */
    public function exists($path)
    {
        return file_exists($path);
    }
    
    /**
     * 
     * @return 
     */
    public function expired($key)
    {
        if (isset($this->params["age"])) {
            $maxAge = (int) $this->params["age"];
            if ($maxAge <= 0) {
                return true;
            } else {
                $age = mktime() - filemtime($this->path);
                return $age > $maxAge;
            }
        } else {
            // No age param means this file never expires
            return false;
        }
    }
    
    public function remove($key)
    {
        //include($this->path);
        $fh = fopen($this->path, 'rb');
        if ($fh !== false) {
            while (!feof($fh)) {
                echo fread($fh, 1024);
            }
        }
    }
    
    /**
     * 
     * @return 
     */
    public function &read()
    {
        ob_start();
        include($this->path);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
    
    /**
     * 
     * @return 
     */
    public function write($content)
    {
        $fh = fopen($this->path, "w+");
        if ($fh) {
            $success = fwrite($fh, $content);
            fclose($fh);
        } else {
            $success = false;
        }
        return $success;
    }
    
    /**
     * 
     * @return 
     */
    public function remove()
    {
        global $settings;
        return unlink($this->path);
    }
}