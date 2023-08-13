<?php

namespace libAllure;

class ElementSelect extends Element
{
    public $options = array();
    private $size = null;

    public function addOption($value, $key = null)
    {
        if ($key === null) {
            $this->options[$value] = $value;
        } else {
            $this->options[$key] = $value;
        }
    }

    public function addOptions($options)
    {
        foreach ($options as $value => $caption) {
            $this->addOption($caption, $value);
        }
    }

    public function render()
    {
        $strOptions = '';

        foreach ($this->options as $key => $val) {
            if ($key === null) {
                $key = $val;
            }

            $sel = ($key == $this->value) ? 'selected = "selected"' : '';

            $strOptions .= sprintf('<option value = "%s" %s>%s</option>', $key, $sel, $val);
        }

        if (!empty($this->onChange)) {
            $onChange = ' onchange = "' . $this->onChange . '()" ';
        } else {
            $onChange = '';
        }

        if ($this->size != null) {
            $size = 'size = "' . $this->size . '"';
        } else {
            $size = null;
        }

        $suggestedValues = array();
        foreach ($this->suggestedValues as $suggestedValue => $caption) {
            if (strpos($suggestedValue, 'jpg') !== -1) {
                $caption = '<img class = "suggestedValue" src = "' . $suggestedValue . '" />';
                $suggestedValue = basename($suggestedValue);
            }

            $suggestedValues[] = '<span class = "dummyLink" onclick = "document.getElementById(\'' . $this->name . '\').value = \'' . $suggestedValue . '\'">' . $caption . '</span>';
        }
        $suggestedValues = implode($suggestedValues);
        if (!empty($suggestedValues)) {
            $suggestedValues = '<p class = "suggestedValueContainer">' . $suggestedValues . '</p>';
        }

        $htmlName = $this->name;

        $multiple = '';

        if (isset($this->multiple)) {
            $multiple = 'multiple';
            $htmlName .= '[]';
        }

        return sprintf('<select id = "%s" %s %s %s name = "%s">%s</select>' . $suggestedValues . '', $htmlName, $onChange, $size, $multiple, $htmlName, $strOptions);
    }

    public function setSize($count)
    {
        if (is_int($count) && $count > 0) {
            $this->size = $count;
        }
    }

    public function jsonSerialize(): mixed
    {
        return array_merge(parent::jsonSerialize(), array(
            'options' => $this->options,
        ));
    }
}
