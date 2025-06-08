<?php

namespace libAllure;

class ElementTextbox extends Element
{
    public $rows = 8;
    public $cols = 80;

    public function render()
    {
        if ($this->value == null) {
            $this->value = '';
        }

        $value = htmlentities($this->value, ENT_QUOTES);
        $value = stripslashes($value);
        $value = strip_tags($value);

        return sprintf('<textarea id = "%s" name = "%s" rows = "%s" cols = "%s">%s</textarea>', $this->name, $this->name, $this->rows, $this->cols, $this->value);
    }
}
