<?php

namespace libAllure;

class ElementCheckbox extends Element
{
    public function getValue()
    {
        return ($this->value == 0) ? 0 : 1;
    }

    public function render()
    {
        $value = ($this->value) ? 'checked = "checked"' : '';
        return sprintf('<div class = "labelHolder"><label for = "%s">%s</label></div><div class = "elementHolder"><input value = "1" type = "checkbox" id = "%s" name = "%s" %s /></div>', $this->name, $this->caption, $this->name, $this->name, $value);
    }
}
