<?php

namespace MVC\Database\V2;

use PDO;
use PDOException;

class Connection 
{
    private PDO $connection;

    public function __construct($config)
    {
        $this->connect($config);
    }

    public function connect($params) 
    {
        try 
        {
            $this->connection = new PDO($params["dsn"], $params["user"], $params["password"]);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } 
        catch (PDOException $e) 
        {
            return $e->getMessage();
        }
    }

    public function get() : PDO 
    {
        return $this->connection;
    }
}