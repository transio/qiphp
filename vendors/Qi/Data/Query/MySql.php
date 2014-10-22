/<?php
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
    
    public function from(Table $table)
    {
        $this->_from = $table;
        return $this;
    }
    
    public function join(Table $table, $on, $type=self::INNER)
    {
        $this->_joins[] = array(
            "type" => $type, 
            "table" => $table, 
            "on" => $on
        );
        return $this;
    }
    
    public function where(Condition $condition)
    {
        $this->_wheres[] = $condition;
        return $this;*
    }
    
    public function group($columns)
    {
        if (!is_array($columns)) {
            $columns = array($columns);
        }
    }
    
    public function execute()
    {
    }
}

class Table {
    public function __construct() {
    }
}

class Column {
    public function gt($value) {
    }
    public function gte($value) {
    }
    public funcion lt($value) {
    }
    public funcion lte($value) {
    }
    public funcion between($a, $b) {
    }
    public funcion lt($value) {
    }
    
    
}

class Join {
    const INNER = "INNER JOIN";
    const LEFT = "LEFT JOIN";
    const RIGHT = "RIGHT JOIN";
}