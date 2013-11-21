<?php
namespace Qi\Log;

/**
 * The Log class is a utility for Qi exception handling and logging.
 */
class Log
{
    // Log levels
    const MESSAGE = "MESSAGE";
    const ERROR = "ERROR";
    const CRITICAL = "CRITICAL";
    
    // Log modes
    const BASIC = "BASIC";
    const TRACE = "TRACE";
    
    /**
     * Constructor
     * @param $filePath Object[optional]
     */
    private function __construct() {}
    
    
    public static function listAll()
    {
        global $settings;
        $dir = new DirectoryIterator($settings->log['path']);
        foreach ($dir as $fileInfo) {
            if (!$fileInfo->isDot()) {
                print($fileInfo->getFilename());
            }
        }
    }
    
    public static function viewAll()
    {
        global $settings;
        $dir = new DirectoryIterator($settings->log['path']);
        foreach ($dir as $fileInfo) {
            if (!$fileInfo->isDot()) {
                self::view($fileInfo);
            }
        }
    }
    
    public static function view(SplFileInfo $fileInfo)
    {
        print("<h3>".$fileInfo->getFilename()."</h3>");
        $file = $fileInfo->openFile();
        print("<pre style=\"width: 100%; height: 400px; overflow: auto; border: solid 1px #ccc;\"><div style=\"padding: 10px;\">");
        foreach ($file as $line) {
            print($line);
        }
        print("</div></pre>");
    }
    
    /**
     * Write a message to log file or email (as appropriat)
     * @return 
     * @param $message Object
     * @param $type Object[optional]
     */
    public static function write($error, $type=self::MESSAGE)
    {
        $retval = false;
        try {
            global $settings;
            $date = date("Y-m");
            $filePath = "{$settings->log['path']}/{$settings->log['prefix']}-{$date}-{$type}.{$settings->log['ext']}";
            
            if (file_exists($filePath) && $file = fopen($filePath, "a+") == true) {
                fclose($file);
                $dateTag = date("Y-m-d H:i:s");
                $ip = isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "";
                $error = str_replace("\n", "\n                      ", $error);
                $error = "[{$dateTag} {$ip}] {$error}\r\n";
                switch ($type) {
                    case self::ERROR:
                        $settings->errorLog .= $error;
                        $retval = error_log($error, 3, $filePath);
                        // TODO - Implement email error logging for critical errors
                        //$retval = error_log($error, 1, $settings->log['email']);
                        break;
                    case self::MESSAGE:
                        $retval = error_log($error, 3, $filePath);
                        break;
                }
            }
        } catch (Exception $e) {
            $retval = false;
        }
        return $retval;
    }
    
    /**
     * Write a formatted log message
     * @return 
     * @param $message Object
     * @param $type Object[optional]
     */
    public static function writeLine($message, $type=self::MESSAGE)
    {
        return self::write($message, $type);
    }
    
    public static function writeLn($message, $type=self::MESSAGE)
    {
        self::write($message, $type);
    }
}
