<?php

namespace libAllure;

class ElementButton extends Element
{
    protected $type = 'submit';

    protected function afterConstruct()
    {
        if (strpos($this->getName(), 'submit') !== false) {
            $this->isSubmitter = true;
        }
    }

    public function render()
    {
        return '<button name = "' . $this->name . '" type = "' . $this->type . '" value = "' . $this->value . '">' . $this->caption . '</button>';
    }
}
