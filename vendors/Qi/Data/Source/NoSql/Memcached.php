<?php
namespace Qi\Data\Source\NoSql;

use \Qi\Data\Source\Compression;
use \Qi\Data\Source\Encoding;

/**
 * Memcached connection class
 */
class Memcached
    extends AbstractConnection
    implements Connection
{

    protected $_servers;
    protected $_connection;

    /**
     * @param array $servers
     * @param array $options
     */
    public function __construct(array $servers, array $options = array())
    {
        // Set the connection settings
        $this->_servers = $servers;

        parent::__construct($options);
    }

    // Create a memcached connection using the given FkConfig settings
    private function _getConnection() {
        if (!$this->_connection) {
            $this->_connection = \Qi\Memcached\Factory::create();
            $this->_connection->addServers($this->_servers);
        }
        return $this->_connection;
    }

    public function get($key)
    {
        $key = $this->_key($key);
        $data = $this->_getConnection()->get($key);
        return $this->_decompress($data);
    }

    public function getMany(array $keys)
    {

        $cache_keys = array();
        foreach ($keys as $key) {
            $cache_keys[$key] = $this->_key($key);
        }

        $data = $this->_getConnection()
                    ->getMulti($cache_keys);

        // Format the data for output
        $out = array();
        foreach ($cache_keys as $key => $cache_key) {
            if (isset($data[$cache_key]))
                $out[$key] = $this->_decompress($data[$cache_key]);
        }
        return $out;
    }

    public function set($key, $value)
    {
        $key = $this->_key($key);
        $value = $this->_compress($value);
        $expires = isset($this->_options['expires']) ? $this->_options['expires'] : 0;

        // Set the value to cache
        if ($expires) {
            return $this->_getConnection()->set($key, $value, $expires);
        } else {
            return $this->_getConnection()->set($key, $value);
        }
    }

    public function setMany(array $key_value_pairs)
    {
        $success = true;
        foreach ($key_value_pairs as $key => $value) {
            $this->set($key, $value);
        }
        return $success;
    }
}
