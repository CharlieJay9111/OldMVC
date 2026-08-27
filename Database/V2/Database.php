<?php

namespace MVC\Database\V2;

use PDO;

class Database 
{
    private Connection $connection;

    private $table;
    private $sql;
    private $result;
    private $params;

    public $queries = [];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    // table
    function table(string $name) 
    {
        $this->table = $name;
        $this->sql = "SELECT * FROM $this->table";
        return $this;
    }

    //insert
    function insert(array $array)
    {
        $query = new Query($this->connection);
        $q = $query->from("fsf")->where("dsfdsf");
        $c = $q->limit(10);

        $values = array_map(fn($value) => ":$value", array_keys($array));
        $values = implode(" , ", $values);

        $cols = array_map(fn($col) => "`$col`", array_keys($array));
        $cols = implode(" , ",  $cols);

        $sql = "INSERT INTO $this->table ($cols) VALUES ($values)";
        $this->queries[] = $sql;
        $statement = $this->connection->get()->prepare($sql); 

        foreach($array as $key => $value){
            $statement->bindValue(":$key", $value);
        }

        $statement->execute();

        return $statement;
    }

    // getLastInsertID
    function getLastInsertID()
    {
        return $this->connection->get()->lastInsertId();
    }

    //update
    function update(array $array, $id)
    {
        $cols = array_map(fn($col) => "$col = :$col", array_keys($array));
        $cols = implode(" , ",  $cols);
        $sql = "UPDATE $this->table SET $cols WHERE $id";
        $this->queries[] = $sql;

        $statement = $this->connection->get()->prepare($sql); 

        foreach($array as $key => $value){
            $statement->bindValue(":$key", $value);
        }

        $statement->execute();

        return $statement;
    }

    // where 
    function where($sql, array $array = null)
    {
        $this->sql .= " WHERE $sql"; 
        if($array) $this->params = $array;
        return $this;
    }

    // select
    function select(string $value)
    {
        $this->sql = "SELECT $value FROM $this->table";
        return $this;
    }

    // order
    function order($value)
    {
        $this->sql .= " ORDER BY $value";
        return $this;
    }

    // limit
    function limit($limit, $offset = 0)
    {
        $this->sql .= " LIMIT $limit OFFSET $offset";
        return $this;
    }

    // bind 
    function bind()
    {
        $statement = $this->connection->get()->prepare($this->sql); 
        $statement->execute($this->params);
        $this->result = $statement;
    }

    // query
    function query()
    {
        $this->queries[] = $this->sql;
        $this->result = $this->connection->get()->query($this->sql);
        return $this;
    }

    // execute 
    function execute()
    {
        $this->queries[] = $this->sql;
        
        if(!isset($this->params))
        {
            $this->result = $this->connection->get()->query($this->sql);
        }
        else
        {
            $this->bind();
        }
    }

    // fetch
    function fetch()
    {
        $this->execute();

        $result = $this->result->fetch(PDO::FETCH_OBJ);

        $this->reset();

        return $result;
    }

    // fetchAll
    function fetchAll()
    {
        $this->execute();

        $result = $this->result->fetchAll(PDO::FETCH_OBJ);

        $this->reset();

        return $result;
    }

    // count
    function count()
    {
        $this->sql = preg_replace("/SELECT.*FROM/", "SELECT count(*) as count FROM", $this->sql);

        $result = $this->fetch()->count;

        $this->reset();

        return $result;
    }

    // delete
    function delete($where){
        $sql = "DELETE FROM $this->table WHERE $where";

        $this->queries[] = $sql;

        $this->connection->get()->query($sql);

        $this->reset();
    }

    // reset 
    function reset()
    {
        unset($this->result, $this->params);
    }
}