<?php

namespace MVC;

class Session
{
    private $name;

    function __construct($name)
    {
        $this->name = $name;
    }

    function __get($name)
    {
        return $_SESSION[$this->name][$name] ?? null;
    }

    function __set($name, $value)
    {
        $_SESSION[$this->name][$name] = $value;
    }

    function add($data)
    {
        foreach($data as $key => $value)
        {
            $this->$key = $value;
        }
    }

    function unset($name = null)
    {
        unset($_SESSION[$this->name][$name]);        
    }

    function remove()
    {
        unset($_SESSION[$this->name]);
    }
}