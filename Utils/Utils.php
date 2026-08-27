<?php

namespace MVC\Utils;

class Utils {


    static function generateToken(){
        return md5(round(microtime(true)).mt_rand());
    }

    static function csrf()
    {
        $token = self::generateToken();
        header("Csrf: $token");
        return $token;
    }

    static function generateLink($link){
        $link = html_entity_decode($link);
        print_r(urldecode($link));
        $characters = [
            "ě" => "e", 
            "š" => "s", 
            "č" => "c",
            "ř" => "r",
            "ž" => "z", 
            "ý" => "y", 
            "á" => "a", 
            "í" => "i", 
            "é" => "e",
            "ú" => "u",
            "ů" => "u",
            "ü" => "u",
            "ň" => "n",
            "ť" => "t",
            "ó" => "o",

            " " => "-",
            " & " => "-",
            "&" => "-",
            " - " => "-",
            " – " => "-",
            "?" => "",
        ];

        $link = mb_strtolower($link);
        echo $link . "<br>";
        $link = strtr($link, $characters);

        return htmlentities($link);
    }

    static function addFolder($path){
        mkdir($path);
    }

    static function upload($file, $path, $name){
        $ext = explode(".", basename($file['name']));
        $ext = $ext[count($ext) - 1];
        $name = $path . $name .".". $ext;
        move_uploaded_file($file['tmp_name'], $name);
        return $name;
    }

    static function multiUpload($files, $path, $name, $index = 0){
        
        set_time_limit(60 * 60);
        
        $names = [];

        for ($i=0; $i < count($files["name"]); $i++) { 
            $file = [];
            $file["name"] = $files["name"][$i];
            $file["tmp_name"] = $files["tmp_name"][$i];
            $names[] = self::upload($file, $path, $name . "-" . $index);
            $index++;
        }

        return $names;
    }

    static function getFiles($path){
        
        if(is_dir($path))
        {
            $files = [];
            $folder = opendir($path);
            while(($file = readdir($folder)) !== false)
            {
                if($file != "." && $file != ".."){
                    $files[] =  $file;
                }
            }
            closedir($folder);

            return $files;
        }

    }
}