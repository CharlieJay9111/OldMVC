<?php 

namespace MVC;

class Request 
{
    private $path;
    private $query;
    private $method;

    private $url;

    public function __construct()
    {
        $url = parse_url($_SERVER['REQUEST_URI']);
        $this->path = $url["path"];
        $this->query = $url["query"] ?? null;
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->url = str_replace("public/index.php", "", $_SERVER["PHP_SELF"]);
        define("URL", $this->url);
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function isGet()
    {
        return $this->getMethod() === "GET";
    }

    public function isPost()
    {
        return $this->getMethod() === "POST";
    }

    public function get($name = null)
    {
        if($name)
        {
            return filter_input(INPUT_GET, $name);
        }

        return filter_input_array(INPUT_GET);
    }

    public function post($name = null)
    {
        if($name)
        {
            return filter_input(INPUT_POST, $name);
        }

        return filter_input_array(INPUT_POST);
    }

    public function files($name = null)
    {
        if($name)
        {
            return $_FILES[$name] ?? null;
        }

        return $_FILES ?? null;
    }
}