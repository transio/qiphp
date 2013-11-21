<?
namespace Qi\File;

 /**
 * File class
 */
class File
{
    const READ_ONLY = "r";          // Opens for reading
    const READ_WRITE = "r+";        // Opens for read/write, cursor at start
    const WRITE_NEW = "w";          // Opens/creates, empties for writing
    const READ_WRITE_NEW = "w+";    // Opens/creates, empties for read/write
    const APPEND = "a";             // Opens/creates, cursor at end
    const READ_APPEND = "a+";       // Opens/creates, cursor at end
    const READ_STRICT = "x";        // Opens for reading, error if no file
    const READ_WRITE_STRICT = "x+"; // Opens for read/write, error if no file

    private $fileHandler = null;
    private $filePath = null;
    private $currentLine = null;
    private $lineNumber = 0;
    protected $newFile = false;
    public $EOF = false;

    public function __construct($filePath=null, $mode=self::READ_APPEND) {
        if (!is_null($filePath)) {
            $this->open($filePath, $mode);
        }
    }
    
    public function __destruct() {
        $this->close();
    }

    public function getMimeType() {
        return mime_content_type($this->filePath);
    }
    
    public function open($filePath, $mode=self::READ_APPEND) {
        if (!self::exists($filePath)) 
            $newFile = true;
        
        $this->filePath = $filePath;
        $this->fileHandler = fopen($filePath, $mode) or die("The file cannot be opened.");
        return true;
    }
    
    public static function openFileAsString($filePath) {
        if (!self::exists($filePath)) $newFile = true;
        return file_get_contents ($filePath);
    }

    public static function loadUploadedFile($name, $targetFolder="upload", $targetName=null) {
        if (is_null($targetName) || !strlen($targetName)) {
            $targetName = $_FILES[$name]["name"];
        } else {
        }
        $targetPath = $targetFolder . "/" . $targetName;
        if(move_uploaded_file($_FILES[$name]["tmp_name"], $targetPath)) {
            return new File($targetPath, self::READ_ONLY);
        } else{
            return false;
        }
    }
    
    public function delete() {
        return unlink ($this->filePath);
    }
    
    public function read($bytes) {
        return fgets($this->fileHandler, $bytes);
    }
    
    public function readAll() {
        return fread($this->fileHandler, $this->getFileSize());
    }

    // Iterator functions
    public function current() {
        return $this->currentLine;
    }
    public function next() {
        $this->currentLine = $this->readLine();
    }
    public function valid() {
        return !feof($this->fileHandler);
    }
    public function rewind() {
        $this->seek(0);
    }
    public function seek($lineNumber) {
        for ($i = 0; $i < $lineNumber; $i++) {
            $this->readLine();
        }
    }

    public function readLine() {
        $line = "";
        while (!feof($this->fileHandler) && $byte != "\n") {
            $byte = fread($this->fileHandler, 1);
            $line .= $byte;
            if ($byte === false) {
                // The byte reader threw an exception
                // Handle it here
            }
        }
        if (feof($this->fileHandler)) $this->EOF = true;
        return $line;
    }
    
    public function write($text) {
        if (is_null($this->fileHandler)) return false;
            fwrite($this->fileHandler, $text);
            return true;
    }
    
    public function writeLine($text) {
        return $this->write($text . "\n");
    }

    public function close() {
        if (is_null($this->fileHandler)) return false;
        try {
                fclose($this->fileHandler);
                        return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getPath() {
        return $this->filePath;
    }

    public function getPathInfo() {
        return pathinfo($this->filePath);
    }
    
    public function getName() {
        $info = $this->getPathInfo();
        return $info["basename"];
    }
    
    public function getFileSize() {
        return filesize($this->filePath);
    }
    
    public static function exists($filePath) {
        return file_exists ($filePath);
    }

    public static function isFile($filePath) {
        return is_file($filePath);
    }
}
