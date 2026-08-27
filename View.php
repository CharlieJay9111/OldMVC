<?php 

namespace MVC;

class View 
{
    public $layout = "layout";
    public $content;

    public $title;
    public $description;
    public $url;
    public $isLogged;

    public $router;

    function __construct()
    {
        $this->url = preg_replace("/\/$/", "", Application::$properties->url);
        $this->router = Application::$properties->router;
        $this->isLogged = Application::$properties->isLogged;
    }

    function render($view, &$data = [], $callback = null, $parrams = null)
    {
        foreach($data as $dataKey => $dataValue){
            $$dataKey = $dataValue;
        }

        
        ob_start();
        require ROOT_PATH . "/app/views/$view.php";
        $this->content = ob_get_clean();

        ob_start();
        require ROOT_PATH . "/app/views/layouts/$this->layout.php";
        $this->content = ob_get_clean();

        if($callback)
        {
            $parrams[] = $this->content;
            $this->content = $callback(...$parrams);
        }
    }

    function component($name, $data = [])
    {
        
    }

    function cssActive($controller)
    {
        if($this->router->getController() == $controller . "Controller")
        {
            return 'class="active"';
        }
    }

    function css($controller)
    {
        if($this->router->getController() == $controller . "Controller")
        {
            return "active";
        }
    }
}