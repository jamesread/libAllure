<?php

require_once 'libAllure/Form.php';
require_once 'libAllure/Template.php';

use \libAllure\Form;
use \libAllure\ElementInput;

use \libAllure\Template;

class FormAskName extends \libAllure\Form {
	public function __construct() {
		parent::__construct('askName', 'Ask Name');

		$this->addElement(new ElementInput('forename', 'Forename?'));
		$this->addElement(new ElementInput('forename', 'Surname?'));

		$this->addDefaultButtons();
	}

	public function process() {
		echo 'Hello ' . $this->getElementValue('forename') . ' ' . $this->getElementValue('surname');
	}
}

$form = new FormAskName();

if ($form->validate()) {
	$form->process();
}

$tpl = new Template('libAllureExamples');
$tpl->displayForm($form);

?>
