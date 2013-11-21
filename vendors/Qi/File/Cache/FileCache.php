<?php

/**
 * The FileCache class is used to cache HTML from Qi Templates
 */
class FileCache
{
    const END_CLEAN = "clean";
    const END_FLUSH = "flush";
    
    private $_writer;
    private $_key;
    private $_value;
    
    /**
     * Constructor
     * @param $moduleType Object[optional]
     * @param $params Object
     */
    public function __construct(\Qi\DataSource\NoSql\Connection $writer, $key)
    {
        $this->_writer = $writer;
        $this->_key = $key;
    }
    
    public function start()
    {
        ob_start();
    }

    public function end($mode=self::END_CLEAN)
    {
        $this->_value = ob_get_contents();
        switch ($mode) {
            case self::END_FLUSH:
                ob_end_flush();
                break;
            case self::END_CLEAN:
            default:
                ob_end_clean();
                break;
        }
    }
    
    public function write()
    {
        $this->_writer->set($this->_key, $this->_value);
    }
}
