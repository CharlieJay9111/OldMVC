<?php 

namespace MVC;

class Router 
{
    private string $path;
    private string $controller;
    private string $action;
    private string $actionName;
    private array $parrams;

    public function __construct(Request $request, $config)
    {
        foreach ($config as $key => $value) {
            $callbacks = explode("->",  $value);
            $this->controller = $callbacks[0];
            $this->action = $callbacks[1];
        }

        $path = explode($request->getUrl(), $request->getPath());
        $path = !empty($path[1]) && $path[1] != "/" ? $path[1] : $this->controller;
        $path = preg_replace("/\/$/", "", $path);
        $parts = explode("/", $path);
        
        $this->controller = ucfirst($parts[0]) . "Controller" ?? $this->controller;
        $this->action = $parts[1] ?? $this->action;

        $this->controller = preg_replace_callback("/-(.)/", function($match){return strtoupper($match[1]);}, $this->controller);
        $this->actionName = preg_replace_callback("/-(.)/", function($match){return strtoupper($match[1]);}, $this->action);

        unset($parts[0],$parts[1]);
        $this->parrams = array_values($parts);

        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getActionName()
    {
        return $this->actionName;
    }

    public function getParrams()
    {
        return $this->parrams;
    }
}