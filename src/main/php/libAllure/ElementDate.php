<?php

namespace libAllure;

class ElementDate extends Element
{
    protected $allowEmpty = true;

    protected function validateInternals()
    {
        $val = $this->getValue();

        if ($this->allowEmpty && empty($val)) {
            return;
        }

        $mathes = array();
        $res = preg_match_all('#\d{4}-\d{2}-\d{2}#', $val, $matches);

        $ts = strtotime($val);

        if (!$res || $ts < 0 || !$ts) {
            $this->setValidationError('That is a not a valid date.');
        }
    }

    public function getValue()
    {
        if (empty($this->value)) {
            return null;
        } else {
            return $this->value;
        }
    }

    public function render()
    {
        $today = new \DateTime();
        $today = $today->format('Y-m-d');

        $buf = null;
        $buf .= sprintf('<div class = "labelHolder"><label for = "%s">%s</label></div><div class = "elementHolder"><input id = "%s" name = "%s" value = "%s" /><span class = "dummyLink" onclick = "javascript:document.getElementById(\'%s\').value=\'%s\'">Today</span></div>', $this->name, $this->caption, $this->name, $this->name, $this->value, $this->name, $today);
        $buf .= <<<JS
<script type = "text/javascript">
	$("#{$this->name}").datepicker({
		dateFormat: "yy-mm-dd", firstDay: 1
	});

</script>
JS;

        return $buf;
    }
}
