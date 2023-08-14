<?php

namespace libAllure;

class ElementDate extends ElementInput
{
    protected $allowEmpty = true;
    protected string $format = 'Y-m-d H:i';

    protected function afterConstruct()
    {
        $today = new \DateTime();
        $today = $today->format($this->format);

        $this->addSuggestedValue($today, 'Today');
    }

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
}
