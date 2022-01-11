<?php

namespace libAllure;

class ElementRadio extends Element
{
    protected $options = array();

    public function addOption($value, $key = null)
    {
        if ($key === null) {
            $this->options[$value] = $value;
        } else {
            $this->options[$key] = $value;
        }
    }

    public function render()
    {
        $strOptions = '';

        foreach ($this->options as $key => $val) {
            if ($key === null) {
                $key = $val;
            }

            $sel = ($key == $this->value) ? 'checked = "checked"' : '';

            $strOptions .= sprintf('<li><label><input type = "radio" name = "%s" value = "%s" %s />%s</label></li>', $this->name, $key, $sel, $val);
        }

        return sprintf('<div class = "labelHolder"><label>%s</label></div><div class = "elementHolder"><ul>%s</ul></div>', $this->caption, $strOptions);
    }
}
