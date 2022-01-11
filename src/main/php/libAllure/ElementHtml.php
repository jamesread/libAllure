<?php

namespace libAllure;

class ElementHtml extends Element
{
    public function render()
    {
        echo $this->value;
    }
}
