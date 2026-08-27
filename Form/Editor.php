<?php

namespace MVC\Form;

class Editor extends Validator 
{

    public string $form;
    public array $inputs = [];
    public string $key;
    public array $names = [];

    public function __construct($method, $data, $model = null)
    {
        parent::__construct($data, $model);
        $this->form = "<form method=\"$method\">{inputs}</form>";
    }

    public function rules($rules){
        $this->rules[$this->key] = $rules;
        return $this;
    }

    public function text($name){
        $this->inputs[$name] = $this->input("text", $name);
        return $this;
    }

    public function password($name){
        $this->inputs[$name] = $this->input("password", $name);
        return $this;
    }

    public function textarea($name){
        $this->inputs[$name] = $this->input("textarea", $name);
        return $this;
    }

    public function input($type, $name){
        $this->key = $name;
        $data = $this->data[$name] ?? null;

        if($type == "textarea"){
            return "<textarea name=\"$name\">$data</textarea>";
        }

        if($data){
            return "<input type=\"$type\" name=\"$name\" value=\"$data\">";
        }

        return "<input type=\"$type\" name=\"$name\">";
    }

    public function select($name, $values){
        $options = "";
        $data = $this->data[$name] ?? "";
        foreach($values as $key => $value){
            $selected = ($data == $key) ? "selected" : "";
            $options .= "<option value=\"$key\" $selected>$value</option>" . PHP_EOL;
        }

        $this->key = $name;
        $this->inputs[$name] = "<select name=\"$name\">" . PHP_EOL . $options . "</select>";
        return $this;
    }

    public function checkbox($name, $value){
        $data = $this->data[$name] ?? "";
        $checked = ($data == $value) ? "checked" : "";
        $this->key = $name;
        $this->inputs[$name] = "<input type=\"checkbox\" name=\"$name\" value=\"$value\" $checked>". PHP_EOL;
        return $this;
    }

    public function radios($name, $values){
        $radios = "";
        $data = $this->data[$name] ?? "";
        foreach($values as $key => $value){
            $checked = ($data == $key) ? "checked" : "";
            $radios .= "<input type=\"radio\" name=\"$name\" value=\"$key\" $checked>". PHP_EOL . "<label>$value</label>" . PHP_EOL;
        }
        $this->key = $name;
        $this->inputs[$name] = $radios;
        return $this;
    }

    public function label($name){
        $this->inputs[$this->key] = "<label>$name</label>" . PHP_EOL . $this->inputs[$this->key];
        return $this;
    }

    public function placeholder($name){
        $this->inputs[$this->key] = str_replace(">", " placeholder=\"$name\">", $this->inputs[$this->key]);
        return $this;
    }

    public function container($container){
        $this->inputs[$this->key] = str_replace("{input}", $this->inputs[$this->key], $container );
        return $this;
    }

    public function button($text){
        $this->inputs["button"] = "<button>$text</button>";
    }

    public function render()
    {
        foreach($this->errors as $key => $error){
            $this->inputs[$key] .= PHP_EOL . "<div class=\"error\">$error[0]</div>";
        }

        $str = implode(PHP_EOL, $this->inputs);
        return str_replace("{inputs}", $str, $this->form);
    }
}