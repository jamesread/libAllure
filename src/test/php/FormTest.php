<?php

require_once 'common.php';
require_once 'libAllure/Form.php';

use \libAllure\Form;
use \libAllure\Element;
use \libAllure\ElementSelect;
use \libAllure\ElementInput;
use \libAllure\ElementHtml;

class EmptyForm extends \libAllure\Form {
	public function __construct($name) {
		parent::__construct($name, 'EmptyForm');
	}
}

class FormTest extends PHPUnit_Framework_TestCase {
	public function testAddingElements() {
		$el = new ElementInput('title', 'description');

		$f = new EmptyForm('testAddingElements');
		$f->addElement($el);

		$this->assertEquals($el, $f->getElement('title'));
	}

	public function testElement() {
		$el = new ElementInput('title', 'caption', 'value', 'description');

		// Name
		$this->assertEquals('title', $el->getName());

		$el->setName('new title');
		$this->assertEquals('new title', $el->getName());

		// Type
		$this->assertEquals('ElementInput', $el->getType());

		// Description
		$this->assertEquals('description', $el->description);

		// Caption
		$this->assertEquals('caption', $el->getCaption());
		$el->setCaption('new capt');
		$this->assertEquals('new capt', $el->getCaption());

		// Value
		$this->assertEquals('value', $el->getValue());
		$el->setValue('val2');
		$this->assertEquals('val2', $el->getValue());
	}

	public function testElementText() {
		$el = new ElementInput('title', 'description');

		$this->assertEquals('title', $el->getName());
	}

	public function testElementHtml() {
		$el = new ElementHtml('test', null, 'foo');
	}

	public function testFormTitle() {
		$f = new EmptyForm('testFormTitle');

		$this->assertEquals('EmptyForm', $f->getTitle());

		$f->setTitle('foo');

		$this->assertEquals('foo', $f->getTitle());
	}

	public function testDefaultButtons() {
		$caption = 'submitButtonCaption';

		$f = new EmptyForm('testDefaultButtons');
		$f->addDefaultButtons($caption);

		$el = $f->getElement('submit');

		$this->assertEquals($caption, $el->getCaption());
	}

	public function testElementSelect() {
		$el = new ElementSelect('test', 'Test');
		$el->addOption('foo');
		$el->addOption('bar');
	}
}

?>
