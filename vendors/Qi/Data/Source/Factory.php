<?php
namespace Flipkey\DataSource;
/**
 * Flipkey Abstract DataSource Factory
 */
class Factory
{

    public static function getPdoConnection($index) {
        $config = \FKConfig::DATABASES($index);

        // Check the db settings for required values
        if (!isset($config['driver']))
            throw new \Exception("Missing required connection parameter 'driver'.");

        if (!isset($config['host']))
            throw new \Exception("Missing required connection parameter 'host'.");

        if (!isset($config['port']))
            throw new \Exception("Missing required connection parameter 'port'.");

        if (!isset($config['database']))
            throw new \Exception("Missing required connection parameter 'database'.");

        // "mysqli" driver is "mysql" in PDO
        if ($config['driver'] == 'mysqli') {
            $config['driver'] = 'mysql';
        }

        $dsn = "{$config['driver']}:" .
            "dbname={$config['database']};" .
            "host={$config['host']};" .
            "port={$config['port']}";

        // Set the optional username and password
        $username = isset($config['login']) ? $config['login'] : null;
        $password = isset($config['password']) ? $config['password'] : null;

        // Set other driver options
        $driver_options = array();

        // Encoding option
        if (isset($config['encoding'])) {
            //$driver_options[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$config['encoding']}";
        }

        // Persistent connection option
        if (isset($config['persistent'])) {
            $driver_options[\PDO::ATTR_PERSISTENT] = (boolean) $config['persistent'];
        }

        // TODO - Check for read-only mode and set alternate user/pass for readonly user if it's set to true
        try {
            return new \PDO($dsn, $username, $password, $driver_options);
        } catch (\Exception $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getMemcachedConnection($index) {
        $options = \FKConfig::NOSQL($index, 'memcached');
        $servers = \FKConfig::MEMCACHED($options['bucket']);
        return new NoSql\MemcachedConnection($servers, $options);
    }

    public static function getCacheBackupConnection($index) {
        $config = \FKConfig::NOSQL($index, 'cache_backup');
        return new NoSql\CacheBackupConnection($config);
    }

}

