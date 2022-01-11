<?php

namespace libAllure;

class ElementEmail extends ElementInput
{
    public function validateInternals()
    {
        parent::validateInternals();

        if (!filter_var($this->getValue(), FILTER_VALIDATE_EMAIL)) {
            $this->setValidationError('This is not a valid email address.');
        }
    }
}
