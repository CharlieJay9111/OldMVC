<?php

namespace MVC\Utils;

class Paginator {

    private $url;
    private $content;

    function __construct($page, $count, $limit, $url, $limitP)
    {
        $c = ceil($count / $limit);
        $paginator = array();
        $this->url = $url;

        if($c == 1){
            return;
        }

        if($page > 1){
            $paginator[] = $this->set(1, "<<<", "b");
            $paginator[] = $this->set($page - 1, "<", "b");
        }

        $e = $page + ($limitP / 2);
        if($e > $c) $e = $c;
        $s = $e - $limitP;
        if($s < 1)
        {
             $s = 1;
             $e = ($e >= $c || $limitP + 1 >= $c) ? $c : $limitP + 1; 
        }
        
        for($i = $s ; $i <= $e ; $i++){
            $active = ($i == $page) ? " active" : "";
            $paginator[] = $this->set($i, $i, $active);
        }

        if($page < $c){
            $paginator[] = $this->set($page + 1, ">", "b");
            $paginator[] = $this->set($c, ">>>", "b");
        }
        
        $this->content = $paginator;
    }

    function get()
    {
        return $this->content;
    }

    function set($page, $value, $active = ""){
        $url = preg_replace('/<page>/', $page, $this->url);
        return '<a href="'.$url.'" class="'.$active.'">'.$value.'</a>';
    }
}