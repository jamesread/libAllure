<?php

namespace libAllure;

class ElementInputRegex extends ElementInput
{
    public const PAT_DEFAULT = '/^[a-z0-9 _]+$/i';
    protected $pat = self::PAT_DEFAULT;

    protected $validationExcuse = ' letters, numbers and english punctuation.';

    public function setPattern($regex, $validationExcuse = null)
    {
        $this->pat = $regex;

        if (!empty($validationExcuse)) {
            $this->validationExcuse = $validationExcuse;
        }
    }

    public function setPatternToIdentifier($additional = '')
    {
        $this->setPattern('#^[a-z][a-z_0-9' . $additional . ']+$#i', 'letters, numbers and underscores');
    }

    public function setPatternToTime()
    {
        $this->setPattern('#^\d{2}\:\d{2}$#', 'a time, like 08:15');
    }

    public function validateInternals()
    {
        parent::validateInternals();

        if (empty($this->value)) {
            return;
        }

        if (!preg_match($this->pat, $this->getValue())) {
            if ($this->pat == self::PAT_DEFAULT) {
                $this->setValidationError('This field may only contain letters, numbers');
            } else {
                $this->setValidationError('This field may only contain ' . $this->validationExcuse . '.');
            }
        }
    }
}
