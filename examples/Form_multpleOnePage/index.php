<?php

date_default_timezone_set('Europe/London');

require_once 'libAllure/Form.php';
require_once 'libAllure/ErrorHandler.php';
require_once 'libAllure/Template.php';

use \libAllure\Form;
use \libAllure\ErrorHandler;
use \libAllure\Template;
use \libAllure\ElementButton;

ErrorHandler::getInstance()->beGreedy();

$tpl = new Template('multFormsLibAllure');

class FormOne extends Form {
	public function __construct() {
		parent::__construct('formBlat');

		$this->addElement(new ElementButton('submit', 'Submit', 'formBlat'));
	}

	public function process() {
		echo 'Process FormOne';
	}	
}

class FormTwo extends Form {
	public function __construct() {
		parent::__construct();

		$this->addDefaultButtons();
	}

	public function process() {
		echo 'Process FormTwo';
	}
}

$f1 = new FormOne();
$f2 = new FormTwo();

if ($f1->validate()) {
	$f1->process();
}

$tpl->displayForm($f1);

if ($f2->validate()) {
	$f2->process();
}

$tpl->displayForm($f2);

var_dump(Form::$registeredForms);

?>
