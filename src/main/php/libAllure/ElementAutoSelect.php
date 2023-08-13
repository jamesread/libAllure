<?php

namespace libAllure;

class ElementAutoSelect extends ElementSelect
{
    public function render()
    {
        $ret = '';

        $ret .= '<input id = "' . $this->name . '" name = "' . $this->name . '" value = "' . $this->value . '">' . '</input>';

        $acId = uniqid();
        $ret .= '<script type = "text/javascript">var ac' . $acId . ' = [';

        foreach ($this->options as $key => $val) {
            $ret .= '{value: "' . $key . '", label: "' . $val . '"},' . "\n";
        }

        $ret .= '];';
        $ret .= 'var sel = function(evt, ui) { $("#' . $this->name . '").val(ui.item.value) };';
        $ret .= '$("#' . $this->name . '").autocomplete({ source: ac' . $acId . ', minLength: 0, select: sel, focus: sel  }); ';
        $ret .= ' $("#' . $this->name . '").data("autocomplete")._renderItem = function(ul, item) { return $("<li />").data("item.autocomplete", item).append("<a>" + item.label + "</a>").appendTo(ul); }';
        $ret .= '</script>';

        return $ret;
    }
}
