<?php

namespace MVC;

class Cookies
{
    function __get($name)
    {
        return $_COOKIE[$name] ?? null;
    }

    function __set($name, $value)
    {
        setcookie($name, $value, time() + (86400 * 30), "/");
    }

    function remove($name)
    {
        setcookie($name, "", time() - 3600); 
    }
}