<?php 

namespace MVC\Form;

use MVC\Debbuger;


class DOMReader extends Validator
{
    private \DOMDocument $dom;

    public array $inputs = [];
    public array $originals = [];
    public string $view;

    public array $fields = [];
    public array $updated = [];

    public array $errorContainers = [];
    public array $containers = [];
    public $error;


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
        $this->dom = new \DOMDocument();
        @$this->dom->loadHTML($view);

        $forms = $this->dom->getElementsByTagName("form");
        $length = $forms->length;

        for($i = 0; $i < $length; $i++)
        {
            $inputs = $this->getFields("input", $forms->item($i));
            $areas = $this->getFields("textarea", $forms->item($i));
            $selects = $this->getFields("select", $forms->item($i));

            $array = array_merge($inputs, $areas, $selects);

            $this->updateFields($array);
        }

        $this->view = $this->dom->saveHTML();
    }

    // get
    private function getElements($name, $parent)
    {
        $elements = $parent->getElementsByTagName($name);
        $length = $elements->length;
        $result = [];

        for($i = 0; $i < $length; $i++)
        {
            $result[] = $elements->item($i);
        }

        return $result;
    }

    private function getFields($name, $parent)
    {
        $elements = $this->getElements($name, $parent);
        $data = [];

        foreach($elements as $element)
        {
            $field = $this->getField($element);
            $data[] = $field;
        }

        $this->fields = array_merge($this->fields, $data);

        return $data;
    }

    private function getField($field)
    {
        $data = new \stdClass;

        $data->name = $field->getAttribute("name");
        if(preg_match("/^([a-zA-Z0-9_]+)\[([a-zA-Z0-9_]*)\]*\[([a-zA-Z0-9_]*)\]*/", $data->name, $matches))
        {
            unset($matches[0]);
            $data->keys = $matches;
        }
        else if(preg_match("/^([a-zA-Z0-9_]+)\[([a-zA-Z0-9_]*)\]*/", $data->name, $matches))
        {
            unset($matches[0]);
            $data->keys = $matches;
        }
        $data->type = ($field->hasAttribute("type")) ? $field->getAttribute("type") : $field->tagName;
        $data->rules = $field->getAttribute("rules");
        $data->element = $field;

        return $data;
    }

    public function get()
    {
        return $this->view;
    }

    // update
    private function updateFields($fields)
    {
        $data = $this->data ?? $this->edit ?? null;



        foreach($fields as $key => $field)
        {
            $this->updateField($field, $data);
        }
    }

    private function updateField($field, $data)
    {
        if(isset($this->updated[$field->name])) return;

        if($data)
        {
            if(isset($field->keys))
            {
                if($field->type != "radio" && $field->type != "checkbox")
                {
                    $value = $this->updateArrayField($field, $data);
                }
                else
                {
                    $value = $this->updateArrayCheckField($field, $data);
                }
                
            }
            else
            {
                $value = $data[$field->name] ?? null;
            }

            $field = $this->updateValue($field, $value ?? null);
        }

        $field = $this->updateError($field);

        //$this->updated[$field->name] = true;
        $this->inputs[$field->name] = $field;
    }

    private function updateArrayField($field, $data)
    {
        $parentEmptyClone = $field->element->parentNode->cloneNode(true);

        $values = $data[$field->keys[1]] ?? [];
        foreach($values as $k => $v)
        {
            if(!is_array($v))
            {
                if($k == 0)
                {
                    $value = $v;
                }
                else
                {
                    $parent = $field->element->parentNode;
                    $parentClone = $parentEmptyClone->cloneNode(true);

                    foreach($parentClone->childNodes as $child)
                    {

                        if($child->nodeName == "#text") continue;

                        if($child->hasAttribute("name"))
                        {
                            $name = $child->getAttribute("name");
                            $element = $this->getField($child);
                            $element = $this->updateValue($element, $v);
                            $this->updated[$element->name] = true;
                        }
                    }

                    $parent->parentNode->appendChild($parentClone);
                }
            }
            else
            {
                $parent = $field->element->parentNode;

                if($k == 0)
                {
                    foreach($parent->childNodes as $child)
                    {
                        if($child->nodeName == "#text") continue;

                        if($child->hasAttribute("name"))
                        {
                            $element = $this->getField($child);
                            $element = $this->updateValue($element, $v[$element->keys[3]]);
                            
                            $this->updated[$element->name] = true;
                        }
                    }
                }
                else
                {
                    $parentClone = $parentEmptyClone->cloneNode(true);

                    foreach($parentClone->childNodes as $child)
                    {

                        if($child->nodeName == "#text") continue;

                        if($child->hasAttribute("name"))
                        {
                            $name = $child->getAttribute("name");
                            $name = preg_replace("/\[[0-9]+\]/", "[$k]", $name);
                            $child->setAttribute("name", $name);
                            $element = $this->getField($child);
                            $element = $this->updateValue($element, $v[$element->keys[3]]);
                            $this->updated[$element->name] = true;
                        }
                    }

                    $parent->parentNode->appendChild($parentClone);
                }
            }
        }

        return $value ?? null;
    }

    private function updateArrayCheckField($field, $data)
    {
        $parent = $field->element->parentNode->parentNode;
        $data = $data[$field->keys[1]] ?? [];

        foreach($parent->childNodes as $child)
        {
            $child = $child->childNodes[1] ?? null;

            if(!$child || $child->nodeName == "#text") continue;

            if($child->hasAttribute("name"))
            {
                $element = $this->getField($child);
                
                $this->updated[$element->name] = true;

                foreach($data as $value)
                {
                    if($value == $element->element->getAttribute("value"))
                    {
                        $element->element->setAttribute("checked", "");
                        break;
                    }
                }

            }
        }
    }

    private function updateValue($field, $value)
    {
        if($value && !is_array($value))
        {
            if($field->type == "textarea")
            {
                $field->element->textContent = $value;
            }
            else if($field->type == "select")
            {
                $options = $this->getElements("option", $field->element);
                foreach($options as $option)
                {
                    if($value == $option->getAttribute("value"))
                    {
                        $option->setAttribute("selected", "");
                    }
                }
            }
            else if($field->type == "checkbox" || $field->type == "radio")
            {
                if($value == $field->element->getAttribute("value"))
                {
                    $field->element->setAttribute("checked", "");
                }
            }
            else
            {
                $field->element->setAttribute("value", $value);
            }

            $field->value = $value;
        }

        return $field;
    }

    private function updateError($field)
    {
        $error = false;
        if($field->name == "csrf")
        {
            $csrf = $this->csrf();
            $field->element->setAttribute("value", $csrf->token);
            if($csrf->error)
            {
                $error = true;
                $this->error = $csrf->error;
            }
        }

        if($error || !$this->validate($field))
        {
            $element = $field->element;
            $errorNode = $this->createError($this->error);

            if($field->type == "checkbox" || $field->type == "radio")
            {
                $parent = $element->parentNode->parentNode;
                $parent->appendChild($errorNode);
            }
            else
            {
                $parent = $element->parentNode;

                if($element->nextSibling)
                {
                    $parent->insertBefore($errorNode, $element->nextSibling);
                }
                else
                {
                    $parent->appendChild($errorNode);
                }
            }

            $field->error = $this->error;
        }

        return $field;
    }

    //
    public function validate($field, $type = null, $keys = null)
    {

        if(!isset($field->rules)) return true;
        $rules = explode(", ", $field->rules ?? null);
        foreach($rules as $rule){
            $rule = trim($rule);
            $value = explode(":", $rule);
            if(isset($value[1])){
                $rule = [$value[0] , $value[0] => $value[1]];
            }
            $this->rules[$field->name][] = $rule;
        }

        if($this->data){
            parent::validate($field->name, $field->type, $field->keys ?? null);
        }

        $this->error = $this->errors[$field->name][0] ?? false;

        return ($this->error) ? false : true;
    }

    private function createError($value) : \DOMNode
    {
        $element = $this->dom->createElement("div", $value);
        $element->setAttribute("class","error");

        return $element;
    }

    public function csrf()
    {
        $data = new \stdClass;

        $data->token = \MVC\Utils\Utils::generateToken();
        $csrf = $_SESSION["csrf"] ?? null;
        $data->error = false;

        if($csrf && $this->data)
        {
            if($csrf != $this->data["csrf"])
            {
                $this->addErrorForRule("csrf", self::RULE_CSRF);
                $data->error = $this->errorMessages()[self::RULE_CSRF];
            }
            else
            {
                unset($this->data["csrf"]);
            }
        }

        $_SESSION["csrf"] = $data->token;
        return $data;
    }

    //
    public function addError($key, $value, $container = false)
    {
        $input = $this->inputs[$key];
        $element = $input->element;

        $errorNode = $this->createError($value);

        if($container)
        {
            $parent = $element->parentNode->parentNode;
            $parent->appendChild($errorNode);
        }
        else
        {
            $parent = $element->parentNode;
            if($element->nextSibling)
            {
                $parent->insertBefore($errorNode, $element->nextSibling);
            }
            else
            {
                $parent->appendChild($errorNode);
            }
        }

        $this->view = $this->dom->saveHTML();
    }

}