<?php
namespace Qi\File\Parser;

/**
 * An extension of the DataParser class.  Reads a Delimited SDF file
 * into an Iterator object.
 */
class SdfParser extends DataParser
{
    /**
     * Loads the data file definition and file object
     * @param $dataFile DataFile An object defining how to parse the file object
     * @param $fileObject SplFileObject Iterator of the file to be parsed
     */
    public function __construct(DataFile &$dataFile, SplFileObject &$fileObject)
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
        // Get the current line of data from the loaded file
        $line = $this->file->current();

        // Create an array into which the line of data will be parsed.
        $data = array();

        // Set the cursor position at 0
        $position = 0;

        // Loop through each expected element by the data file format given
        // and transform each element encountered in the line into a datum
        // and add them to the datum array.
        foreach ($this->dataFile->getElements() as $element) {
            $name = $element->getName();
            $length = $element->getLength();
            $start = $element->getStart();
            $end = $element->getEnd();

            try {
                // Select the value as a substring of the inputted line of text
                $start--;
                if (is_numeric($start) && is_numeric($end) && $start >= 0 && $end > $start) {
                    $value = substr($line, $start, $end-$start);
                } else {
                    throw new Exception ("Invalid Data Element Found");
                }

                // TODO - Implement trimming rules for SDF
                $value = trim($value);

                // Convert the datum
                $value = DataConverter::convertData($value, $element);
            } catch (Exception $e) {
                // If an exception was found handle it
                // TODO - Handle exceptions better
                if ($element->getDataConversionHandlerId() == DataConversionHandler::FAIL) {
                    throw($e);
                } else {
                    // Ignore data conversion failures if specified to do so
                }
            }

            // Add the value to the datum array indexed by its Element Name
            $data[$name] = $value;

            // Deprecated in version 1.5 - associate by Element ID
            //$data[$element->getId()] = $value;

            // Increment the current cursor position by the value length
            $position = $position + $length;

            // TODO - Check file format to make sure it matches exactly...
            // if not... Raise exception?  Log exception?
        }
        return $data;
    }
}
