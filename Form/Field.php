<?php

namespace MVC\Form;

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
