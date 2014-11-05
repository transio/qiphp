<?php
namespace Qi\Data\Source\NoSql;
/**
 * Created by IntelliJ IDEA.
 * User: smoseley
 * Date: 3/22/13
 * Time: 10:23 AM
 * To change this template use File | Settings | File Templates.
 */
interface Connection
{
    public function get($key);

    public function getMany(array $keys);

    public function set($key, $value);

    public function setMany(array $key_value_pairs);

}
