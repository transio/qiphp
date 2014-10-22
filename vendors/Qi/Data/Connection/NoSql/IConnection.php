<?php
namespace Qi\Data\Source\NoSql;
/**
 * @author - Steven Moseley
 */
interface IConnection
{
    public function get($key);

    public function getMany(array $keys);

    public function set($key, $value);

    public function setMany(array $key_value_pairs);

}
