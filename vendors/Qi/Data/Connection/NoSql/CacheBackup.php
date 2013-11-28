<?php
namespace Qi\Data\Source\NoSql;

/**
 * CacheBackup Connection
 */
class CacheBackup
    extends AbstractConnection
    implements Connection
{

    protected $_config;
    protected $_connection;

    public static $defaults = array(
        'table'         => null,
        'database'      => 'cache_backup',
        'key_column'    => 'id',
        'key_type'      => \PDO::PARAM_INT,
        'data_column'   => 'data',
    );

    public function __construct(array $config)
    {
        // Set the connection settings
        $this->_config = $config;

        parent::__construct($config);
    }

    private function _getConnection()
    {
        if (!$this->_connection) {
            $this->_connection = \Qi\Data\Source\Factory::getPdoConnection($this->_config['database']);
        }
        return $this->_connection;
    }

    public function get($key)
    {
        $sql = "SELECT
                    `{$this->_config['key_column']}`,
                    `{$this->_config['data_column']}`
                FROM `{$this->_config['table']}`
                WHERE `{$this->_config['key_column']}` = :key";

        // Prepare statement and bind key param
        $statement = $this->_getConnection()->prepare($sql);
        $statement->bindParam(':key', $this->_key($key), $this->_config['key_type']);

        // Execute
        if ($statement->execute()) {
            if ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                return $this->_decompress($row[$this->_config['data_column']]);
            }
        }
    }

    public function getMany(array $keys)
    {
        // Implode a list of key params for IN clause
        $key_params = array();
        foreach ($keys as $i => $key) {
            $key_params[] = ":key{$i}";
        }
        $key_params = implode(",", $key_params);

        // Create and prepare statement with IN clause
        $sql = "SELECT
                    `{$this->_config['key_column']}`,
                    `{$this->_config['data_column']}`
                FROM `{$this->_config['table']}`
                WHERE `{$this->_config['key_column']}` IN ({$key_params})";
        $statement = $this->_getConnection()->prepare($sql);

        // Bind keys to their respective params
        foreach ($keys as $i => $key) {
            $statement->bindParam(":key{$i}", $this->_key($key), $this->_config['key_type']);
        }

        // Execute
        if ($statement->execute()) {
            $result = array();
            while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $key = $row[$this->_config['key_column']];
                $value = $row[$this->_config['data_column']];
                $result[$key] = $this->_decompress($value);
            }
            return $result;
        }
    }

    public function set($key, $value)
    {
        // Create the basic insert / update statement
        $sql = "INSERT INTO `{$this->_config['database']}`.`{$this->_config['table']}` (
                    `{$this->_config['key_column']}`,
                    `{$this->_config['data_column']}`
                ) VALUES (
                    :key,
                    :value
                ) ON DUPLICATE KEY UPDATE
                    `{$this->_config['data_column']}` = :value";

        // Compress the data value
        $value = $this->_compress($value);

        // Prepare the statement
        $statement = $this->_getConnection()->prepare($sql);
        $statement->bindParam(':key', $this->_key($key), $this->_config['key_type']);
        $statement->bindParam(':value', $value);

        // Execute the statement and return the # of rows modified
        return $statement->execute();
    }

    public function setMany(array $key_value_pairs)
    {
        foreach ($key_value_pairs as $key => $value) {
            $this->set($key, $value);
        }
    }
}
