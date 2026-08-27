<?php 

namespace MVC;

class Debbuger 
{

    static function print($value)
    {
        echo "<pre>";
        print_r($value);
        echo "</pre>";
    }
}