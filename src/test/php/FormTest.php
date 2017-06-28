<?php

require_once 'common.php';
require_once 'libAllure/Form.php';
require_once 'libAllure/FormHandler.php';

use \libAllure\Form;
use \libAllure\Element;
use \libAllure\ElementSelect;
use \libAllure\ElementInput;
use \libAllure\ElementHtml;
use \libAllure\ElementInputRegex;

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

	public function testElementSelectSingle() {
		$el = new ElementSelect('test', 'Test');
		$el->addOption('foo');
		$el->addOption('bar');

		$f = new EmptyForm('testSelect');
		$f->addElement($el);
		$f->addDefaultButtons();

		$_POST['testSelect-submit'] = 'testSelect-submit';
		$_POST['testSelect-test'] = 'bar';

		$f->validate();

		$this->assertTrue($f->isSubmitted());
		$this->assertEquals('bar', $f->getElementValue('test'));
	}

	public function testElementSelectMultiple() {
		$el = new ElementSelect('test', 'Test');
		$el->multiple = true;
		$el->addOption('foo');
		$el->addOption('bar');

		$f = new EmptyForm('testSelectMultiple');
		$f->addElement($el);
		$f->addDefaultButtons();

		$_POST['testSelectMultiple-submit'] = 'testSelectMultiple-submit';
		$_POST['testSelectMultiple-test'] = array('foo', 'bar');

		$f->validate();

		$this->assertTrue($f->isSubmitted());
		$this->assertContains('bar', $f->getElementValue('test'));
		$this->assertContains('foo', $f->getElementValue('test'));

	}

	public function testIdentifier() {
		$el = new ElementInputRegex('name', 'Name');
		$el->setPatternToIdentifier();

		$el->setValue('foobar');
		$el->validateInternals();
		$this->assertNull($el->getValidationError());

		$el->setValue('FOOBAR');
		$el->validateInternals();
		$this->assertNull($el->getValidationError());

		$el->setValue('fFOOBAR10');
		$el->validateInternals();
		$this->assertNull($el->getValidationError());

		$el->setValue('1Foobar');
		$el->validateInternals();
		$this->assertNotNull($el->getValidationError());

	}

}

?>
