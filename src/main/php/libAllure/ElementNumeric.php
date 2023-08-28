<?php

namespace libAllure;

class ElementNumeric extends ElementInput
{
    private $allowNegative = false;
    private $allowFloatingPoint = false;
    private $maximum = PHP_INT_MAX;
    private $minimum = 0;

    public function setAllowNegative($allowNegative)
    {
        $this->allowNegative = $allowNegative;
        $this->minimum = ($this->allowNegative) ? -PHP_INT_MAX : 0;
    }

    public function setAllowFloatingPoint($allowFloatingPoint)
    {
        $this->allowFloatingPoint = $allowFloatingPoint;
    }

    public function setBounds($minimum, $maximum)
    {
        if (!is_numeric($minimum) || !is_numeric($maximum)) {
            throw new \Exception('The minimum and maximum values on a ElementNumeric must also be numeric.');
        }

        if ($maximum < $minimum) {
            throw new \Exception('The maximum value on an ElementNumeric is less than the minimum!');
        }

        $this->minimum = $minimum;
        $this->maximum = $maximum;
    }

    public function validateInternals()
    {
        if (empty($this->value) && (!$this->required || $this->maximum == 0)) {
            return;
        }

        if (!is_numeric($this->value)) {
            $this->setValidationError('A number is required.');
        }

        if (!$this->allowNegative && floatval($this->value) < 0) {
            $this->setValidationError('Only positive values are allowed.');
        }

        if (!$this->allowFloatingPoint && is_float($this->value)) {
            $this->setValidationError('Floating point values are not allowed, use integers (whole numbers).');
        }

        // denery

        if (floatval($this->value) > $this->maximum || floatval($this->value) < $this->minimum) {
            if ($this->maximum == PHP_INT_MAX) {
                $this->setValidationError('A number, 0 or larger, is required.');
            } else {
                $this->setValidationError('A number between ' . $this->minimum . ' and ' . $this->maximum . ' is required');
            }
        }
    }
}
