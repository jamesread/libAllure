<?php

date_default_timezone_set('Europe/London');

set_include_path(get_include_path() . PATH_SEPARATOR . '../../src/main/php/' . PATH_SEPARATOR . '../../vendor/');

require_once 'autoload.php';

use \libAllure\Form;
use \libAllure\ElementInput;

use \libAllure\Template;

class FormAskName extends \libAllure\Form {
	public function __construct() {
		parent::__construct('askName', 'Ask Name');

		$this->addElement(new ElementInput('forename', 'Forename'));
		$this->addElement(new ElementInput('surname', 'Surname'));

		$el = new ElementInput('salary', 'Salary');
		$el->AddSuggestedValue('100,000', 'Developer');
		$el->AddSuggestedValue('200,000', 'Manager');
		$this->addElement($el);

		$this->addDefaultButtons();
	}

	public function process() {
		echo 'Hello ' . $this->getElementValue('forename') . ' ' . $this->getElementValue('surname');
		echo '<br />';
		echo 'Your salary is ' . $this->getElementValue('salary');
	}
}

$form = new FormAskName();

if ($form->validate()) {
	$form->process();
}

$tpl = new Template('libAllureExamples');
$tpl->displayForm($form);

?>
