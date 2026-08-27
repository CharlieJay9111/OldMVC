<?php 

namespace MVC\Form;


class FormReader extends Validator
{
    public array $inputs = [];
    public array $originals = [];
    public string $view;

    public array $errorContainers = [];
    public array $containers = [];

    public function readFile($name, $data = [])
    {
        foreach($data as $dataKey => $dataValue){
            $$dataKey = $dataValue;
        }

        ob_start();
        require ROOT_PATH . "/app/views/" . $name . ".php";
        $content = ob_get_clean();

        $this->read($content);
    }

    public function read($view)
    {
        $this->view = $view;
        preg_match("/<form.*<\/form>/s", $view, $form);


        foreach($form as $match)
        {
            if(preg_match('<input type="hidden" name="csrf">', $match))
            {
                $this->view = str_replace('<input type="hidden" name="csrf">', $this->csrf(), $this->view);
            }

            preg_match_all('/<div id="([a-z]+)-inputs">/', $match, $inputs);

            foreach($inputs[1] as $k => $v){
                $this->containers[$inputs[1][$k]] = [true];
            }


            preg_match_all('/<input type="([a-z]+)" name="(?<name>.[^"]+)".*>/', $match, $inputs);

            $this->addInputs($inputs);

            preg_match_all('/<textarea name="(?<name>.[^"]+)".*>/', $match, $inputs);

            $this->addInputs($inputs, "textarea");

            preg_match_all('/<select name="(?<name>.[^"]+)".*>.*<\/select>/s', $match, $inputs);

            $this->addInputs($inputs, "select");
        }



        foreach($this->containers as $key => $value)
        {
            $replace = '<div id="'. $key . '-inputs">';

            unset($value[0]);

            $a = [];

            foreach($value as $key2 => $value2)
            {
                $last = array_key_last($value2);

                foreach($value2 as $key3 => $value3)
                {
                    if($last == $key3) continue;
                    $replace .= PHP_EOL . $value3;
                }
            }

            $replace .= '</div><div id="'. $key . '-inputs">';
    
            $this->view = str_replace('<div id="'. $key . '-inputs">', $replace, $this->view);
        }

        $this->view = preg_replace('/<div error=".[^"]+"><\/div>/', "", $this->view);
    }

    public function addError($name, $text, $view)
    {
        if(preg_match("/<div error=\"$name\"><\/div>/", $view))
        {
            return preg_replace("/<div error=\"$name\"><\/div>/", "<div class=\"error\">$text</div>", $view);
        }

        return preg_replace("/(<.*name=\"$name\".*>)/", "$1" . "<div class=\"error\">$text</div>" , $view);
    }

    public function addErrorTo($name, $value)
    {
        if(preg_match("/<div error=\"$name\"><\/div>/", $this->view))
        {
            $this->view = preg_replace("/<div error=\"$name\"><\/div>/", "<div class=\"error\">$value</div>", $this->view);
        }

        $this->view = preg_replace("/(<.*name=\"$name\".*>)/", "$1" . "<div class=\"error\">$value</div>" , $this->view);
    }

    public function addInputs($inputs, $types = null){
        for ($i=0; $i < count($inputs[0]); $i++) { 
            $input = $inputs[0][$i];
            $name = $inputs["name"][$i];
            $type = $types ?? $inputs[1][$i];

            $this->addInput($input, $name , $type);
        }
    }

    public function addInput($input, $name, $type)
    {
        if(preg_match("/\[\]/", $name))
        {
            $name = explode("[]", $name)[0];

            if(preg_match("/\[([a-z_]+)\]/", $name, $match))
            {
                $name = explode("[", $name)[0];
                $secondKey = $match[1];
                if(!isset($this->originals[$name][$secondKey]))
                {
                    $this->originals[$name][$secondKey] = [];
                }

                $this->originals[$name][$secondKey][] = $input;
                $firstKey = array_key_last($this->originals[$name][$secondKey]);
            }
            else
            {
                $this->originals[$name][] = $input;
                $firstKey = array_key_last($this->originals[$name]);
            }
        }
        else
        {
            $this->originals[$name] = $input;
        }
        
        preg_match('/rules="(.[^"]+)"/', $input, $rules );
        if($rules){
            $rules = explode(",", $rules[1]);
            foreach($rules as $rule){
                $rule = trim($rule);
                $value = explode(":", $rule);
                if(isset($value[1])){
                    $rule = [$value[0] , $value[0] => $value[1]];
                }
                $this->rules[$name][] = $rule;
            }

            if($this->data){
                $this->validate($name, $secondKey ?? null);
            }

        }
        
        $data = $this->data[$name] ?? $this->edit[$name] ?? false;
        $error = $this->errors[$name][0] ?? false;
        if($error)
        {
            if(isset($this->errorContainers[$name]))
            {
                $error = false;
            }
            else if(preg_match("/<div error=\"$name\"><\/div>/", $this->view))
            {
                $this->view = preg_replace("/<div error=\"$name\"><\/div>/", "<div class=\"error\">$error</div>", $this->view);
                $this->errorContainers[$name] = true;
                $error = false;
            }
        }
        
        if($data && isset($firstKey))
        {
            if(isset($secondKey))
            {
                $replace = "";

                foreach($data[$secondKey] as $k => $value)
                {
                    $this->inputs[$name][$k] = new Field($input, $name, $type, $value, $error);

                    if(isset($this->containers[$name]))
                    {
                        $this->containers[$name][$secondKey][$k] = $this->inputs[$name][$k]->input;
                    }

                    $replace = $this->inputs[$name][$k]->input;
                }                
            }
            else
            {
                $replace = "";

                foreach($data as $k => $value)
                {
                    $this->inputs[$name][$k] = new Field($input, $name, $type, $value, $error);
                    $replace .= $this->inputs[$name][$k]->input;
                }
            }
        }
        else
        {
            $this->inputs[$name] = new Field($input, $name, $type, $data, $error);
            $replace = $this->inputs[$name]->input;
        }

        $this->view = str_replace($input, $replace, $this->view);


    }

    public function get()
    {
        return $this->view;
    }
}

class Field {
    public string $input;
    public string $name;
    public string $type;
    public $value;

    public function __construct($input, $name, $type, $value, $error, $key = null, $index = null)
    {
        $input = preg_replace('/rules="(.[^"]+)"/', " " , $input);

        $this->input = $input;
        $this->name = $name;
        $this->type = $type;

        // if(is_array($value))
        // {   
        //     if($type == "checkbox")
        //     {
        //         $data = $value;
        //         $value = false;

        //         preg_match('/value="(.[^"]+)"/', $input, $match);
        //         foreach($data as $v)
        //         {
        //             if($v == $match[1])
        //             {
        //                 $value = true;
        //             }
        //         }
        //     }
        //     else if($type == "text")
        //     {
        //         $a = [];

        //         if($index !== null) $value = $value[$index];

        //         foreach($value as $i => $v)
        //         {
        //             $a[] = preg_replace("/>/", " value=\"$v\">", $input);
        //         }

        //         $this->input = implode(PHP_EOL, $a);

        //         $value = false;
        //     }
        //     else if($type == "textarea")
        //     {
        //         $a = [];

        //         if($index !== null) $value = $value[$index];

        //         foreach($value as $i => $v)
        //         {
        //             $a[] = preg_replace("/>(.)*</", ">$v<", $input);
        //         }

        //         $this->input = implode(PHP_EOL, $a);

        //         $value = false;
        //     }
        // }

        $this->value = $value;

        if($value){
            if($type == "textarea"){
                $this->input = preg_replace("/>(.)*</", ">$value<", $input);
            }
            elseif($type == "select"){
                $this->input = preg_replace("/value=\"$value\"/", "value=\"$value\" selected", $input);
            }
            elseif($type == "checkbox"){
                $this->input = preg_replace("/>/", " checked>", $input);
            }
            elseif($type == "radio"){
                $this->input = preg_replace("/value=\"$value\"/", "value=\"$value\" checked", $input);
            }
            else {
                $this->input = preg_replace("/>/", " value=\"$value\">", $input);
            }
            
        }

        if($error){
            
            $this->input .= PHP_EOL . "<div class=\"error\">$error</div>";
        }
    }
}
