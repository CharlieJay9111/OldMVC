<?php 

namespace MVC\Form;


class Validator {

    public const RULE_REQUIRED = "required";
    public const RULE_EMAIL = "email";
    public const RULE_PHONE = "phone";
    public const RULE_MIN = "min";
    public const RULE_MAX = "max";
    public const RULE_MATCH = "match";
    public const RULE_UNIQUE = "unique";
    public const RULE_CSRF = "csrf";
    public const RULE_HTML = "html";

    public const TYPE_TEXT = "text";
    public const TYPE_FILE = "file";

    public $data = [];
    public $edit = [];
    public $copy;

    public array $files = [];

    private $model;


    public array $errors = [];
    public array $rules = [];

    public function __construct($data, $model = null)
    {
        $this->data = $data ?? null;
        $this->model = $model;
        
        if(!$this->data) return;

        foreach($this->data as $key => $value)
        {
            if(!is_array($value))
            {
                $value = trim($value);
                $value = htmlspecialchars($value);
                $this->data[$key] = $value;
            }
            else
            {
                foreach($value as $key2 => $value2)
                {
                    if(!is_array($value2))
                    {
                        $value2 = trim($value2);
                        $value2 = htmlspecialchars($value2);
                        $this->data[$key][$key2] = $value2;
                    }
                    else
                    {
                        foreach($value2 as $key3 => $value3)
                        {
                            if(!is_array($value3))
                            {
                                $value3 = trim($value3);
                                $value3 = htmlspecialchars($value3);
                                $this->data[$key][$key2][$key3] = $value3;
                            }
                        }
                    }
                }
            }
        }

    }

    public function files($values)
    {
        $this->files = $values;
    }

    public function validates(){
        if(!$this->data && !$this->edit) return;

        foreach ($this->rules as $key => $value) {
            $this->validate($key);
        }

        return empty($this->errors);
    }

    public function validate($attribute, $type = null, $keys = null)
    {


        if($keys)
        {
            $value = $this->data ?? $this->edit;
            foreach ($keys as $key) 
            {
                if($key !== null && $key != "")
                {
                    $value = $value[$key] ?? null;
                }
                else
                {
                    $value = $value[0] ?? null;
                }
            }
        }
        else
        {
            $value = $this->data[$attribute] ?? $this->edit[$attribute] ?? null;
        }

        
       
        if(is_array($value))
        {
            $value = $value[0] ?? null;
        }
        foreach($this->rules[$attribute] as $rule){
            $ruleName = $rule;
            if(!is_string($ruleName)){
                $ruleName = $rule[0];
            }

            if($type == self::TYPE_FILE)
            {
                $file = $this->files[$attribute] ?? null;
                if($file && $file["error"]){
                    $edit = $this->edit[$attribute] ?? null;
                    if(!$edit)
                    {
                        $this->addErrorForRule($attribute, self::RULE_REQUIRED);
                    }
                }

                return;
            }

            if ($ruleName === self::RULE_HTML) {
                $this->data[$attribute] = htmlspecialchars_decode($this->data[$attribute]);
            }

            if($ruleName === self::RULE_REQUIRED && !$value){
                $this->addErrorForRule($attribute, self::RULE_REQUIRED);
                continue;
            }

            if($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)){
                $this->addErrorForRule($attribute, self::RULE_EMAIL);
                continue;
            }

            if($ruleName === self::RULE_PHONE && !preg_match("/^[0-9 +()]+$/", $value)){
                $this->addErrorForRule($attribute, self::RULE_PHONE);
                continue;
            }

            if ($ruleName === self::RULE_MIN && strlen($value) < $rule['min']) {
                $this->addErrorForRule($attribute, self::RULE_MIN, $rule);
                continue;
            }

            if ($ruleName === self::RULE_MAX && strlen($value) > $rule['max']) {
                $this->addErrorForRule($attribute, self::RULE_MAX, $rule);
                continue;
            }

            if ($ruleName === self::RULE_MATCH && $value !== $this->data[$rule['match']]) {
                $this->addErrorForRule($attribute, self::RULE_MATCH, $rule);
                continue;
            }
            
            if ($ruleName === self::RULE_UNIQUE) {
                $uniqueAttr = $rule['attribute'] ?? $attribute;
                
                if(!$this->copy && $this->edit && $this->edit[$uniqueAttr] == $value)
                {
                    continue;
                }

                if ($this->model->exists($uniqueAttr, $value)) {
                    $this->addErrorForRule($attribute, self::RULE_UNIQUE, ["field" => $attribute]);
                    continue;
                }
            }
        }
    
        return empty($this->errors);
    }

    public function edit($data)
    {
        $this->edit = (array) $data;
    }

    public function copy($data)
    {
        $this->edit($data);
        $this->copy = true;
    }

    public function addErrorForRule(string $attribute, string $rule, $params = [])
    {
        $message = $this->errorMessages()[$rule] ?? '';
        foreach ($params as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }
        $this->errors[$attribute][] = $message;
    }

    public function getErrorMessages($key)
    {
        return $this->errorMessages()[$key];
    }

    public function errorMessages()
    {
        return [
            self::RULE_REQUIRED => 'Toto pole je povinné',
            self::RULE_EMAIL => 'Toto pole musí být platná e-mailová adresa',
            self::RULE_PHONE => 'Toto pole musí obsahovat platné telefonní číslo',
            self::RULE_MIN => 'Minimální délka tohoto pole musí být {min}',
            self::RULE_MAX => 'Maximální délka tohoto pole musí být {max}',
            self::RULE_MATCH => 'Toto pole musí být stejné jako {match}',
            self::RULE_UNIQUE => 'Záznam již existuje',
            self::RULE_CSRF => 'Neplatný token',
            // self::RULE_REQUIRED => 'This field is required',
            // self::RULE_EMAIL => 'This field must be valid email address',
            // self::RULE_PHONE => 'This field must be valid phone number',
            // self::RULE_MIN => 'Min length of this field must be {min}',
            // self::RULE_MAX => 'Max length of this field must be {max}',
            // self::RULE_MATCH => 'This field must be the same as {match}',
            // self::RULE_UNIQUE => 'Record with this {field} already exists',
            // self::RULE_CSRF => 'Invalid token',
        ];
    }

    public function csrf()
    {
        $token = \MVC\Utils\Utils::generateToken();
        $input = "<input type='hidden' name='csrf' value='$token'>";
        $csrf = $_SESSION["csrf"] ?? null;

        if($csrf && $this->data)
        {
            if($csrf != $this->data["csrf"])
            {
                $this->addErrorForRule("csrf", self::RULE_CSRF);
                $error = $this->errorMessages()[self::RULE_CSRF];
                $input .= PHP_EOL . "<div class=\"error\">$error</div>";
            }
            else
            {
                unset($this->data["csrf"]);
            }
        }

        $_SESSION["csrf"] = $token;
        return $input;
    }

    public function isValidate()
    {
        if(!$this->data) return false;
        if($this->errors) return false;
        return true;
    }
}