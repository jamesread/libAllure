<?php

namespace libAllure;

class ElementInput extends Element
{
    protected $minLength = 4;
    protected $maxLength = 64;


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

        $suggestedValues = array();

        if (!empty($this->suggestedValues)) {
            foreach ($this->suggestedValues as $suggestedValue => $caption) {
                $suggestedValues[] = '<span class = "dummyLink" onclick = "document.getElementById(\'' . $this->name . '\').value = \'' . $suggestedValue . '\'">' . $caption . '</span>';
            }
        }

        return sprintf('<input %s id = "%s" name = "%s" value = "%s" />%s</div>', $onChange, $this->name, $this->name, $value, implode(', ', $suggestedValues));
    }

    public function validateInternals()
    {
        $val = trim($this->getValue());
        $length = strlen($val);

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
