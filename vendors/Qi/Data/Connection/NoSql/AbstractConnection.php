<?php
namespace Qi\Data\Source\NoSql;

use Qi\Compression\Compression;

abstract class AbstractConnection
{
    public static $default_options = array(
        'prefix'        => '',
        'compression'   => Compression::COMPRESSION_DEFLATE,
        'encoding'      => Compression::ENCODING_JSON,
        'expires'       => 0
    );
    protected $_options;

    public function __construct(array $options = array())
    {
        $this->_options = array_merge(self::$default_options, $options);
    }

    /**
     * Formats the key for the NoSQL connection
     * @param $key
     * @return string
     */
    protected function _key($key)
    {
        return (is_array($this->_options) && isset($this->_options['prefix']) ? $this->_options['prefix'] : '') . $key;
    }

    /**
     * Compress the data for storage
     * @param $data
     * @return mixed
     * @deprecated - Use Compression::compress() instead
     */
    protected function _compress($data)
    {
        return Compression::compress($data, $this->_options['compression'], $this->_options['encoding']);
    }

    /**
     * Decompress stored data for use
     * @param $data
     * @return mixed|null|string
     * @deprecated - Use Compression::decompress() instead
     */
    protected function _decompress($data) {
        return Compression::decompress($data, $this->_options['compression'], $this->_options['encoding']);
    }
}