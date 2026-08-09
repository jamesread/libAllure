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

        $rows = max(1, (int) $this->rows);
        $cols = max(1, (int) $this->cols);

        return sprintf('<textarea id = "%s" name = "%s" rows = "%d" cols = "%d">%s</textarea>', $this->name, $this->name, $rows, $cols, $value);
    }
}
