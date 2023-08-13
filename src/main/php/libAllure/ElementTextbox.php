<?php

namespace libAllure;

class ElementTextbox extends Element
{
    public function render()
    {
        if ($this->value == null) {
            $this->value = '';
        }

        $value = htmlentities($this->value, ENT_QUOTES);
        $value = stripslashes($value);
        $value = strip_tags($value);

        return sprintf('<textarea id = "%s" name = "%s" rows = "8" cols = "80">%s</textarea>', $this->name, $this->name, $this->value);
    }
}
