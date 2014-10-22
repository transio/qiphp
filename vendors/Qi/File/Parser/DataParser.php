<?php
namespace Qi\File\Parser;

/**
 * An abstract class used to parse and iterate different types of
 * data files.
 */
abstract class DataParser implements \Iterator
{
    protected $dataFile = null;
    protected $errorMessage = null;
    protected $file = null;
    protected $data = null;
    
    /**
     * Constructor
     * Loads the data file definition and file object
     * @param $dataFile DataFile An object defining how to parse the file object
     * @param $fileObject SplFileObject Iterator of the file to be parsed
     */
    public function __construct(DataFile &$dataFile, \SplFileObject $file)
    {
        $this->file = $file;
        $this->dataFile = $dataFile;
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        unset($this->dataFile);
        unset($this->file);
        unset($this->data);
    }
    
    public static function getDataParser(DataFile &$dataFile, SplFileObject $file)
    {
        $parser = null;
        if ($file == null) return;

        // Data File Type handler.
        switch ($dataFile->getDataFileTypeId()) {
            case DataFileType::SDF:
                $parser = new SdfParser($dataFile, $file);
                break;
            case DataFileType::CSV:
                $parser = new CsvParser($dataFile, $file);
                break;
            case DataFileType::XML:
                $parser = new XmlParser($dataFile, $file);
                break;
            default:
                //$parser = new DataParser($dataFile, $file);
        }
        return $parser;
    }
    
    /**
     * Iterator rewind() implementation
     */
    public function rewind()
    {
        $this->file->rewind();
    }

    /**
     * Iterator valid() implementation
     */
    public function valid()
    {
        return $this->file->valid();
    }
    
    /**
     * Iterator current() implementation.  This method is overrided in child classes.
     */
    public function current()
    {
        return $this->file->current();
    }

    /**
     * Iterator key() implementation
     */
    public function key()
    {
        return $this->file->key();
    }

    /**
     * Iterator next() implementation
     */
    public function next()
    {
        $this->file->next();
    }

    public function seek($lineNumber)
    {
        $this->file->seek($lineNumber);
    }
}
