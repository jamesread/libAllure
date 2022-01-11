<?php

namespace libAllure;

class ElementAlphaNumeric extends ElementInputRegex
{
    public function setPunctuationAllowed($isAllowed)
    {
        if ($isAllowed) {
            $this->pat = '/^[\w _\-\.,!]+$/i';
        } else {
            $this->pat = self::PAT_DEFAULT;
        }
    }
}
