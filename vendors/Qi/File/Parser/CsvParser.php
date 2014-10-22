<?php
namespace Qi\File\Parser;

/**
 * An extension of the DataParser class.  Reads a Delimited CSV file
 * into an Iterator object.
 */
class CsvParser extends DataParser
{
    /**
     * Constructor
     * Loads the data file definition and file object
     * @param $dataFile DataFile An object defining how to parse the file object
     * @param $fileObject SplFileObject Iterator of the file to be parsed
     */
    public function __construct(DataFile &$dataFile, \SplFileObject &$fileObject)
    {
        parent::__construct($dataFile, $fileObject);
    }
    
    /**
     * Destructor
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Overloads the Iterator current() method.  Loads the current line of data,
     * and parses the SDF by looping through the DataFile->Elements collection
     * and extracting the data as defined, and finally returning it as an array.
     * @return an associative array (by DataElement name) of parsed data
     */
    public function current()
    {
        // Read a single line of the file into a string
        $line = $this->file->current();

        // If no delimiter is specified, use comma as default
        $delimiter = strlen($this->dataFile->getColDelimiter()) ? $this->dataFile->getColDelimiter() : ",";

        // Split the string into elements
        $items = CsvDataParser::parseCsv($line, $delimiter);

        // Create the data array for export
        $data = array();

        // Data elements array by reference
        $dataElements = &$this->dataFile->getElements();

        /*
        Next version should implement a delimiter and an enclosure
        $delimiter = strlen($this->dataFile->getDelimiter()) ? $this->dataFile->getDelimiter() : ",";
        $enclosure = strlen($this->dataFile->getEnclosure()) ? $this->dataFile->getEnclosure() : ",";
        */

        // If the size of the current line of data doesn't match the definition, throw an exception
        if (count($items) != count($dataElements)) {
            echo "Count error " . count($items) . "-" . count($dataElements) . "\n";
            //$this->log->writeLine("CSV data does not match DataFile definition");
            throw new Exception("CSV data does not match DataFile definition");
        }

        // Merge the data into an associative array by dataelement names
        for ($i = 0; $i < count($items); $i++) {
            $data[$dataElements[$i]->getName()] = $items[$i];
        }
        unset($items);

        // Loop through the data elements
        foreach ($dataElements as $element) {
            try {
                // Get the current value, trim it, and convert it.
                $value = &$data[$element->getName()];
                $value = trim($value);
                $value = DataConverter::convertData($value, $element);

            } catch (Exception $e) {
                // If an exception was found handle it
                if ($element->getDataConversionHandlerId() == DataConversionHandler::FAIL) {
                    throw($e);
                } else {
                    // Ignore data conversion failures if specified to do so
                }
            }
        }
        return $data;
    }
    
    private static function parseCsv($str, $delimiter = ",", $enclosure = "\"", $len = 4096)
    {
        $fh = fopen('php://memory', 'rw');
        fwrite($fh, $str);
        rewind($fh);
        $result = fgetcsv( $fh, $len, $delimiter, $enclosure );
        fclose($fh);
        return $result;
    }
    
    private static function parseCsv2()
    {
        $handle = fopen('somefile.csv', 'r');
        if ($handle) {
            set_time_limit(0);
            $fields = fgetcsv($handle, 4096, ',');
            //loop through one row at a time
            while (($data = fgetcsv($handle, 4096, ',')) !== false) {
                $data = array_combine($fields, $data);
            }
            fclose($handle);
        }
    }
}
