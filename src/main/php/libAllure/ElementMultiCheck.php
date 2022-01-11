<?php

namespace libAllure;

class ElementMultiCheck extends Element
{
    private $values;

    public function addOption($key, $value = null)
    {
        $value = ($value == null) ? $key : $value;

        $this->values[$key] = $value;
    }

    public function getValue()
    {
        $v = parent::getValue();

        if ($v === null) {
            return array();
        } else {
            return $v;
        }
    }

    public function render()
    {
        $ret = '<div class = "labelHolder"><label>' . $this->caption . '</label></div><div class = "elementHolder"><ul>';
        foreach ($this->values as $key => $label) {
            $checked = (in_array($key, $this->getValue())) ? 'checked = "checked" ' : '';
            $ret .= sprintf('<li><input type = "checkbox" name = "%s[]" value = "%s" %s /> <label for = "%s">%s</label></li>', $this->name, $key, $checked, $this->name, $label);
        }
        $ret .= '</ul></div>';

        return $ret;
    }
}
