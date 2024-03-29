<?php

namespace libAllure;

class ElementInput extends Element
{
    protected $minLength = 4;
    protected $maxLength = 64;

    public $type = "text";
    public $extraHtml = "";

    public function render()
    {
        $onChange = (empty($this->onChange)) ? null : 'onkeyup = "' . $this->onChange . '()"';

        if ($this->value == null) {
            $this->value = '';
        }

        $value = htmlentities($this->value, ENT_QUOTES);
        $value = stripslashes($value);
        $value = strip_tags($value);

        $classes = ($this->required) ? ' class = "required" ' : null;

        return sprintf('<input type = "%s" %s %s id = "%s" name = "%s" value = "%s" />', $this->type, $this->extraHtml, $onChange, $this->name, $this->name, $value);
    }

    protected function validateInternals()
    {
        $val = $this->getValue();

        if ($val == null) {
            $length = 0;
        } else {
            $val = trim($val);
            $length = strlen($val);
        }

        // Check $val after trim() incase it's just whitespace
        if (empty($val) && !$this->required) {
            return;
        }

        if ($length < $this->minLength) {
            $this->setValidationError('You should enter more than ' . $this->minLength . ' characters, this is ' . $length . ' characters long.');
            return;
        }

        if ($length > $this->maxLength) {
            $this->setValidationError('You may not enter more than ' . $this->maxLength . ' characters, this is ' . $length . ' characters long.');
            return;
        }
    }

    public function setMinMaxLengths($minLength, $maxLength)
    {
        $this->minLength = $minLength;
        $this->maxLength = $maxLength;
    }
}
