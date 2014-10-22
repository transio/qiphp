<?php
namespace Qi\DataParser;

/**
 * An extension of the DataParser class.  Reads a Delimited XML file
 * into an Iterator object.
 */
class XmlParser extends DataParser
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
     * Overloads the Iterator current() method.  Loads the current line of data.
     *
     * This is not currently implemented.  It will not likely be required until
     * a much later version of the software is released.  It will use DOM to parse
     * the XML file, and will require a multi-dimensional parsing architecture in
     * order to properly handle the data.
     * @return an associative array (by DataElement name) of parsed data
     */
    public function current()
    {
        return parent::current();
    }
}
