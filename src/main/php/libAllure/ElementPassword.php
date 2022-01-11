<?php

namespace libAllure;

class ElementPassword extends Element
{
    private $minLength = 6;
    private $maxLength = 128;

    public function setOptional($isOptional = true)
    {
        if ($isOptional) {
            $this->minLength = 0;
        } else {
            $this->minLength = 6;
        }
    }

    public function validateInternals()
    {

        $length = strlen($this->getValue());

        if ($length < $this->minLength) {
            $this->setValidationError('You should enter more than ' . $this->minLength . ' characters, this is ' . $length . ' characters long.');
            return;
        }

        if ($length > $this->maxLength) {
            $this->setValidationError('You may not enter more than ' . $this->maxLength . ' characters, this is ' . $length . ' characters long.');
            return;
        }
    }

    public function render()
    {
        return sprintf('<div class = "labelHolder"><label for = "%s">%s</label></div><div class = "elementHolder"><input %s id = "%s" name = "%s" type = "password" /></div>', $this->name, $this->caption, (($this->required == true) ? 'class = "required"' : null), $this->name, $this->name);
    }
}
