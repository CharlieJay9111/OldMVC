<?php

namespace MVC\Utils;

class Image
{
    private $object;
    public $filename;
    public $type;
    public $size;

    function __construct($filename)
    {
        $this->filename = $filename;
        $this->size = getimagesize($this->filename);
        $this->type = $this->size[2];
        $this->object = $this->open();
    }

    function open()
    {
        switch($this->type)
        {
            case IMAGETYPE_WEBP: return imagecreatefromwebp($this->filename); break;
            case IMAGETYPE_GIF: return imagecreatefromgif($this->filename); break;
            case IMAGETYPE_JPEG: return imagecreatefromjpeg($this->filename); break;
            case IMAGETYPE_PNG: return imagecreatefrompng($this->filename); break;
            default: return false;
        }
    }

    function resize($width, $height)
    {
        if ($this->size[0] > $width || $this->size[1] > $height )
        {
            $result = ($this->size[0] / $this->size[1]) * $height ;
            if ($result > $width)
            {
                $result = ($this->size[1] / $this->size[0]) * $width ;
                $height = $result ;
            }
            else
            {
                $width = $result;
            }
        }

        $old = $this->object;
        $this->object = imagecreatetruecolor($width,$height);

        return imagecopyresampled($this->object, $old, 0, 0, 0, 0, $width, $height, $this->size[0], $this->size[1]);
    }

    function crop($x, $y, $width, $height)
    {
        return imagecrop($this->object, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
    }

    function convert($type)
    {
        $this->type = $type;
    }

    function save($to, $quality = 80)
    {
        switch($this->type)
        {
            case IMAGETYPE_WEBP: return imagewebp($this->object, $to, $quality); break;
            case IMAGETYPE_GIF: return imagegif($this->object, $to, $quality); break;
            case IMAGETYPE_JPEG: return imagejpeg($this->object, $to, $quality); break;
            case IMAGETYPE_PNG: return imagepng($this->object, $to, $quality); break;
            default: return false;
        }
    }
}

