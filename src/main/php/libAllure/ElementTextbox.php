<?php

namespace libAllure;

class ElementTextbox extends Element
{
    public function render()
    {

        $value = htmlentities($this->value, ENT_QUOTES);
        $value = stripslashes($value);
        $value = strip_tags($value);

        return sprintf('<div class = "labelHolder"><label for = "%s">%s</label></div><div class = "elementHolder"><textarea id = "%s" name = "%s" rows = "8" cols = "80">%s</textarea></div>', $this->name, $this->caption, $this->name, $this->name, $this->value);
    }
}
