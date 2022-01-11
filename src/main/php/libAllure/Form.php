<?php

/*******************************************************************************

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*******************************************************************************/

namespace libAllure;

use libAllure\Sanitizer;

if (!function_exists('array_flatten')) {
    function array_flatten(array $o)
    {
        $ret = array();

        foreach ($o as $k => $a) {
            if (is_array($a)) {
                $ret = array_merge($ret, array_flatten($a));
            } else {
                $ret[$k] = $a;
            }
        }

        return $ret;
    }
}

abstract class Form
{
    public static $registeredForms = array();

    private $rules = array();
    protected $elements = array();
    public $scripts = array();
    private $name;
    private $submitter;
    private $title = '';
    private $generalError;
    private $action;

    protected $enctype = 'multipart/form-data';

    public static $fullyQualifiedElementNames = true;

    public const BTN_LOGIN = 1;
    public const BTN_RESET = 2;
    public const BTN_SUBMIT = 4;

    public function __construct($name = null, $title = null, $action = null)
    {
        if (empty($name)) {
            $name = get_class($this);
        }

        if (in_array($name, self::$registeredForms)) {
            throw new \Exception('Construction of a duplicate form, each form should have a unique name (' . $name . ')');
        } else {
            self::$registeredForms[] = $name;
        }

        $this->name = $name;
        $this->title = empty($title) ? get_class($this) : $title;

        if ($action == null) {
            $action = $_SERVER['PHP_SELF'];
        }

        $this->action = $action;
    }

    public function orderElements()
    {
        $newOrder = func_get_args();
        $oldOrder = $this->elements;
        $this->elements = array();

        foreach ($newOrder as $element) {
            $element = $this->getElementName($element);

            $this->elements[$element] = $oldOrder[$element];
        }

        $this->elements = array_merge($this->elements, $oldOrder);
    }

    public function setFullyQualifiedElementNames($newVal)
    {
        if (count($this->elements) > 0) {
            throw new \Exception('Cannot change form FQFN after elements have been added.');
        }

        self::$fullyQualifiedElementNames = $newVal;
    }

    /**
    Encapsulate a string within <script> tags and dump it
    at the bottom of the form.
    */
    public function addScript($s)
    {
        $this->scripts[] = $s;
    }

    public function addSection($sectionTitle)
    {
        $this->addElement(new ElementHtml('sectionTitle' . uniqid(), null, '<p class = "formSection">' . $sectionTitle . '</p>'));
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function requireFields()
    {
        $requiredFields = func_get_args();

        if (count(func_get_args()) == 1 && is_array($requiredFields[0])) {
            $requiredFields = $requiredFields[0];
        }

        foreach ($requiredFields as $field) {
            $this->getElement($field)->setRequired(true);
        }
    }

    public function process()
    {
        throw new \Exception('this form (' . get_class($this) . ') has an empty process handler.');
    }

    public function getElementValue($elementName)
    {
        global $db;

        return Sanitizer::getInstance()->escapeStringForClean($this->getElement($elementName)->getValue());
    }

    public function bindStatementValues(&$stmt, $names)
    {
        foreach ($names as $paramName => $elementName) {
            if (is_numeric($paramName)) {
                $paramName = $elementName;
            }

            $stmt->bindValue(':' . $paramName, $this->getElementValue($elementName));
        }
    }

    public function bindElementToStatement(&$stmt, $elementName, $parameterName = null)
    {
        $el = $this->getElement($elementName);

        if ($parameterName == null) {
            $parameterName = ':' . $elementName;
        }

        $stmt->bindValue($parameterName, $el->getValue());
    }

    public function addDefaultButtons($title = null)
    {
        $this->addButtons(Form::BTN_SUBMIT);

        if (!empty($title)) {
            $this->getElement('submit')->setCaption($title);
        }
    }

    /**

    A simple function to add buttons to a form. Buttons are specified as a bitmask,
    the values for which should be defined at the top of this file.

    */
    public function addButtons($buttonMask)
    {
        $buttons = array();

        if ($buttonMask & Form::BTN_LOGIN) {
            $buttons[] = new ElementButton('submit', 'Login', $this->name);
        }

        if ($buttonMask & Form::BTN_SUBMIT) {
            $buttons[] = new ElementButton('submit', 'Submit', $this->name);
        }

        $this->addElementGroup($buttons);
    }

    public function addElement(Element $el)
    {
        return $this->addElementImpl($el);
    }

    public function addElementHidden($name, $value)
    {
        return $this->addElement(new ElementHidden($name, null, $value));
    }

    public function addElementReadOnly($title, $value, $roElementName = null)
    {
        if (!empty($roElementName)) {
            $this->addElement(new ElementHidden($roElementName, null, $value));
        }

        if (empty($value)) {
            $value = '<em>not set</em>';
        }

        return $this->addElement(new ElementHtml(uniqid(), null, '<fieldset><div class = "labelHolder"><label>' . $title . '</label></div><div class = "elementHolder">' . $value . '</div></fieldset>'));
    }

    public function addElementDetached(Element $el)
    {
        $oldValue = self::$fullyQualifiedElementNames;
        self::$fullyQualifiedElementNames = false;
        $this->addElementImpl($el);
        self::$fullyQualifiedElementNames = $oldValue;
    }

    private function addElementImpl($el)
    {
        if ($el->isSubmitter()) {
            $this->submitter = &$el;
        }

        $newName = $this->getElementName($el->getName());
        $el->setName($newName);

        $this->elements[$newName] = &$el;

        return $el;
    }

    private function getElementName($element)
    {
        if (self::$fullyQualifiedElementNames) {
            return $this->name . '-' . $element;
        } else {
            return $element;
        }
    }

    public function setGeneralError($generalError)
    {
        $this->generalError = $generalError;
    }

    public static function strToForm($s)
    {
        if (class_exists($s)) {
            $i = new $s();

            if ($i instanceof Form) {
                return $i;
            } else {
                throw new \Exception('Found form class for (' . $s . ') but, it isnt a form instance!');
            }
        } else {
            throw new \Exception('Str to Form failed, the class does not exist: ' . $s);
        }
    }

    public function addElementGroup(array $elementList)
    {
        foreach ($elementList as $el) {
            if ($el->isSubmitter()) {
                $this->submitter = &$el;
            }

            if (self::$fullyQualifiedElementNames) {
                $el->setName($this->name . '-' . $el->getName());
            }
        }

        // groups are added without a name
        $this->elements[] = $elementList;
    }

    public function getElement($name)
    {
        if (self::$fullyQualifiedElementNames) {
            $internalName = $this->name . '-' . $name;
        } else {
            $internalName = $name;
        }

        foreach (array_flatten($this->elements) as $el) {
            if ($el->getName() == $internalName) {
                return $el;
            }
        }

        throw new \Exception('Could not find element "' . $name . '" from form elements.');
    }

    public function setElementError($el, $err)
    {
        $this->getElement($el)->setValidationError($err);
    }

    public function isSubmitted()
    {
        if (!isset($this->submitter)) {
            throw new \Exception('Cannot check if a form is submitted, as no element on the form is a valid submitter.');
        }

        if (isset($_POST[$this->submitter->getName()])) {
            if ($this->submitter->getValue() == $this->name) {
                return true;
            }
        }

        return false;
    }

    /**
    This will not reset element groups or hidden elements.
    */
    final public function reset()
    {
        unset($_POST[$this->submitter->getName()]);

        foreach ($this->elements as $e) {
            if ($e instanceof Element && !($e instanceof ElementHidden)) {
                $e->setValue(null);
            }
        }
    }

    public function getDisplay()
    {
        ob_start();
        $this->display();
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }

    final public function validate()
    {
        if (!$this->isSubmitted()) {
            return false;
        }

        foreach (array_flatten($this->elements) as $e) {
            $name =  $e->getName();

            if ($e instanceof ElementCheckBox && !isset($_POST[$name])) {
                // If checkboxes are not checked browsers do not include them in
                // $_POST, but we have them in our form, so set to 0.
                $e->setValue(0);
            } elseif ($e instanceof ElementButton || $e instanceof ElementHtml) {
                // These elements cannot have values.
            } else {
                if (isset($_POST[$name])) {
                    $e->setValue($_POST[$name]);
                } else {
                    \libAllure\Logger::messageWarning('Could not set element value on: ' . $name . ' which is a ' . get_class($e));
                }
            }

            $e->validate();
        }

        $this->validateExtended();

        // The errors will not have been detected, so lets see
        // if there are any errors.
        foreach (array_flatten($this->elements) as $el) {
            if ($el->getValidationError() != null) {
                return false;
            }
        }

        return true;
    }

    protected function validateExtended()
    {
    }

    public function getEnctype()
    {
        return $this->enctype;
    }

    public function getScripts()
    {
        return $this->scripts;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setAllElementValues($values)
    {
        if (!isset($values['id'])) {
            throw new \Exception('setAllElementValues must have an index with the item id (id, not itemID).');
        }

        try {
            $this->getElement('itemId')->setValue($values['id']);
        } catch (\Exception $e) {
            throw new \Exception('Form (' . get_class($this) . ') must contain a itemId element if using setAllElementValues');
        }

        foreach ($this->elements as $elName => $el) {
            if (isset($values[$elName])) {
                $el->setValue($values[$elName]);
            }
        }
    }

    public function getAllElementValues()
    {
        $values = array();

        foreach (array_flatten($this->elements) as $el) {
            $values[$el->getName()] = $el->getValue();
        }

        return $values;
    }

    public function getElements()
    {
        return $this->elements;
    }
}
