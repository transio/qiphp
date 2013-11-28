<?php
namespace Qi\Data\Query;
/**
 * Qi MySql query builder
 */
class MySql
{
    private $_from;
    private $_joins = array();
    
    public function __construct(\PDO $pdoConnection)
    {
    }
    
    public function from($table)
    {
        $this->_from = $table;
        return $this;
    }
    
    public function join($table, $on, $type=self::INNER)
    {
        $this->_joins[] = array(
            "type" => $type, 
            "table" => $table, 
            "on" => $on
        );
        return $this;
    }
    
    public function where($condition)
    {
        $this->_wheres[] = $condition;
        return $this;*
    }
    
    public function execute()
    {
    }
}

class Join {
    const INNER = "INNER JOIN";
    const LEFT = "LEFT JOIN";
    const RIGHT = "RIGHT JOIN";
}