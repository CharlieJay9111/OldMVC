<?php 

namespace MVC;


class Application 
{
    private Request $request;
    private Router $router;
    private Loader $loader;

    private Controller $controller;

    public static \stdClass $properties;

    public function __construct()
    {   
        //session_cache_limiter('public');
        session_start();

        $this->loader = new Loader();

        $this->request = new Request();
        require ROOT_PATH . "/app/config/router.php";
        $this->router = new Router($this->request, $config);

        $this->loader->request = $this->request;
        $this->loader->router = $this->router;
        $this->loader->action = $this->router->getAction();
        $this->loader->actionName = $this->router->getActionName();
        $this->loader->parrams = $this->router->getParrams();
        $this->loader->url = $this->request->getUrl();

        $user = new User();
        $this->loader->isLogged = $user->isLogged();

        self::$properties = new \stdClass;
        self::$properties->url = $this->request->getUrl();
        self::$properties->router = $this->router;
        self::$properties->isLogged = $user->isLogged();

        $action = $this->router->getActionName();        
        $controllerName = "\app\controllers\\" . $this->router->getController();
        if(class_exists($controllerName))
        {
            $this->controller = $this->loader->init($controllerName);        

            if($this->controller->getView()) return;

            if(method_exists($this->controller, $action))
            {   
                if($this->loader->initMethod($this->controller, $action)) return;
            }
            
            if(method_exists($this->controller, "any"))
            {
                if($this->loader->initMethod($this->controller, "any")) return;
            }
        }

        $this->controller = new Controller;
        $this->controller->notFound();
    }

    public function run()
    {
        // $age = 60 * 60 * 1;
        // header("Cache-Control: max-age=$age, must-revalidate");

        // // $time = gmdate(("D, d M Y H:i:s"));
        // // header("Last-Modified: $time");

        // ob_start();
        // $this->controller->render();
        
        // header("Content-Length: ". ob_get_length());

        // echo ob_get_clean();

        $this->controller->render();
    }
}