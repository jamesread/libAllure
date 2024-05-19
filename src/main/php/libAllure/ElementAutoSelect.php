<?php

namespace libAllure;

class ElementAutoSelect extends ElementSelect
{
    public function render()
    {
        $ret = '';

        $acId = uniqid();

        $ret .= '<input id = "' . $this->name . '" name = "' . $this->name . '" value = "' . $this->value . '" list = "' . $acId . '">' . '</input>';

        $ret .= '<datalist id = "' . $acId . '">';

        foreach ($this->options as $key => $val) {
            $ret .= '<option value = "' . $key . '">' . $val . '</option>';
        }

        $ret .= '</datalist>';

        return $ret;
    }
}
