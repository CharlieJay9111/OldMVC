<?php 

namespace MVC;

class Controller 
{
    protected View $view;

    protected function view($name, &$data = [], $callback = null, $parrams = null)
    {
        $this->view = new View();
        $this->view->render($name, $data, $callback, $parrams);
    }

    function render()
    {
        echo $this->view->content;
    }

    public function getView()
    {
        return $this->view ?? null;
    }

    public function notFound()
    {
        http_response_code(404);
        $this->view("errors/404");
    }

    function redirect($path = null)
    {
        if($path)
        {
            $path = Application::$properties->url . $path;
        }
        
        header("Location: $path");
    }
}