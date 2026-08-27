<?php 

namespace MVC;

use MVC\Database\Database;

abstract class Model {
    protected Database $db;
    protected $tableName;
    

    function __construct(Database $database){
        $this->db = $database;
    }

    protected function table($name = null) : Database {
        if(!$name) $name = $this->tableName;
        return $this->db->table($name);
    }

    public function exists($name, $value)
    {
        return ($this->table()->where("$name = ?", [$value])->fetch()) ? true : false;
    }
}