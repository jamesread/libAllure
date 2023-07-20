<?php

namespace libAllure;

abstract class Element implements \JsonSerializable
{
    protected $name;
    protected $caption;
    protected $value;
    protected $enabled;
    protected $required = false;
    protected $isSubmitter = false;
    protected $suggestedValues = array();

    public $description;
    public $suffix;

    private $validationErrorMessage = null;

    /**
    The Javascript function to call onChange (or similar).

    You cannot set this value from the constructor so it has a default value.
    */
    protected $onChange = '';

    public function __construct($name, $caption, $value = null, $description = null, $suffix = null, $enabled = true)
    {
        $this->name = $name;
        $this->caption = $caption;
        $this->value = $value;
        $this->description = $description;
        $this->suffix = $suffix;
        $this->enabled = $enabled;

        $this->afterConstruct();
    }

    protected function validateInternals()
    {
    }
    protected function afterConstruct()
    {
    }
    abstract public function render();

    public function addSuggestedValue($value, $caption = null)
    {
        $caption = (empty($caption)) ? $value : $caption;

        $this->suggestedValues[$value] = $caption;
    }

    final public function validate()
    {
        $this->validateRequired();
        $this->validateInternals();
    }

    private function validateRequired()
    {
        if ($this->required) {
            $val = $this->getValue();
            if ($val == null || $val == '') {
                $this->setValidationError('This field is required.');
            }
        }
    }

    final public function getType()
    {
        $typeWithNamespace = get_class($this);
        $components = explode("\\", $typeWithNamespace);
        $class = $components[count($components) - 1];

        return $class;
    }

    final public function setName($newName)
    {
        $this->name = $newName;
    }

    final public function setValue($value)
    {
        $this->value = $value;
    }

    final public function setRequired($v)
    {
        $this->required = ($v === true);
    }

    final public function isRequired()
    {
        return $this->required;
    }

    final public function setCaption($c)
    {
        $this->caption = $c;
    }

    final public function getCaption()
    {
        return $this->caption;
    }

    final public function setOnChange($onChange)
    {
        $this->onChange = $onChange;
    }

    final public function getName()
    {
        return $this->name;
    }

    final public function isSubmitter()
    {
        return $this->isSubmitter;
    }

    final public function setValidationError($validationErrorMessage)
    {
        if (empty($this->validationErrorMessage)) {
            $this->validationErrorMessage = $validationErrorMessage;
        }
    }

    final public function getValidationError()
    {
        return $this->validationErrorMessage;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function jsonSerialize(): mixed
    {
        return array(
            'name' => $this->name,
            'caption' => $this->caption,
            'description' => $this->description,
            'required' => $this->required,
            'type' => $this->getType(),
        );
    }
}
