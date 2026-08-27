<?php

namespace MVC\Database\V2;


class Query 
{
    private Connection $connention;

    private $sql;

    private $select = "*";
    private $from;

    private $where;
    private $attributes;

    private $limit;
    private $order;

    function __construct(Connection $connection)
    {
        $this->connention = $connection;
    }

    public function select($value)
    {
        $this->select = $value;
        return $this;
    }

    public function from($value)
    {
        $this->from = $value;
        return $this;
    }

    public function where($value, ...$attributes)
    {
        $this->where = $value;
        $this->attributes = $attributes;
        return $this;
    }

    public function limit($value, $offset = 0)
    {
        $this->limit = [$value, $offset];
        return $this;
    }

    public function order($value)
    {
        $this->order = $value;
        return $this;
    }

    private function create()
    {
        $this->sql = "SELECT " . $this->select . " FROM " . $this->from;

        if($this->where)
        {
            $this->sql .= " WHERE " . $this->where;
        }

        if($this->order)
        {
            $this->sql .= " ORDER BY " . $this->order;
        }

        if($this->limit)
        {
            $this->sql .= " LIMIT " . $this->limit[0] . " OFFSET " . $this->limit[1];
        }

        return $this->sql;
    }

    
}