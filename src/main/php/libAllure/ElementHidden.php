<?php

namespace libAllure;

class ElementHidden extends Element
{
    public function isVisible()
    {
        return false;
    }

    public function render()
    {
        return '<input name = "' . $this->name . '" type = "hidden" value = "' . $this->value . '" />';
    }
}
