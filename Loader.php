<?php

namespace MVC;

class Loader 
{
    private \stdClass $properties;

    function __construct()
    {
        $this->properties = new \stdClass;
    }

    function __set($name, $value)
    {
        $this->properties->$name = $value;
    }

    function init($class)
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        if($constructor)
        {
            $params = $constructor->getParameters();
            $values = $this->getValues($params, $class);

            return new $class(...$values);
        }
        
        return new $class;
    }

    function initMethod($class, $method)
    {
        $reflection = new \ReflectionMethod($class, $method);

        if($reflection->isPrivate()) return false;

        $params = $reflection->getParameters();
        $values = $this->getValues($params, $class);

        return call_user_func([$class, $method], ...$values) ?? true;
    }

    private function getValues($params, $className)
    {
        $values = [];
        foreach ($params as $key => $value) {

            $name = $value->name;
            $type = $value->getType();

            if($name == "config")
            {   
                $fileName = explode('\\',  $className);
                $fileName = lcfirst(end($fileName));
                require ROOT_PATH . "/app/config/$fileName.php";
                $values[] = $config;
            }
            elseif(!isset($this->properties->$name))
            {
                $className = $type ? $type->getName() : 'MVC\\' . ucfirst($name);
                $this->properties->$name = $this->init($className);
                $values[] = $this->properties->$name;
            }
            else
            {
                $values[] = $this->properties->$name;
            }
        }

        return $values;
    }
}