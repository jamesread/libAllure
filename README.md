libAllure
==

libAllure is a library I wrote to abstract-away concrete implementations of libraries that I use in a lot of PHP applications I write. 

It's a mostly shim library, important functionality relies on well maintained, big libraries, with a possible exception from the session code which is custom. The library is in use by approximately 15+ applications that I know of, it's pretty portable, and you could swap out implementations quite easily if you don't like my choices.


[![Travis build Status](https://travis-ci.org/jamesread/libAllure.png?branch=master)](https://travis-ci.org/jamesread/libAllure)

Database.php
---
Wrapper around **PDO**.

	require_once 'libAllure/Database.php';

	use \libAllure\Database;

	$database = new Database('mysql:dbname=testdb;host=127.0.0.1', 'username', 'password');

	$sql = 'SELECT p.id, p.title FROM products p';
	$results = $database->prepare($sql)->execute();

	var_dump($results->fetchAll());

ErrorHandler.php
---
Custom error handler that complains at the slightest thing, makes debugging nice and easy.

	require_once 'libAllure/ErrorHandler.php';

	use \libAllure\ErrorHandler;

	$handler = new ErrorHandler();
	$handler->beGreedy();

	throw new Exception('This is a test');

Form.php
---
Custom form handling code. 

	require_once 'libAllure/Form.php';
	require_once 'libAllure/Template.php';

	use \libAllure\ElementInput;
	use \libAllure\Template;

	$tpl = new Template('myTemplates'); // requires form.tpl and formElements.tpl in your templates folder

	class MyForm extends \libAllure\Form {
		public function __construct() {
			$this->addElement(new ElementInput('forename', 'Forename', 'My Default Name');
			$this->addDefaultButtons():
		}

		public function process() {
			// do something
		}
	}

	$f = new MyForm();

	if ($f->validate()) {
		$f->process();
	}

	$tpl->displayForm($f);

Template.php
---
Just a nice wrapper around Smarty2/3, that adds in a few compatibility functions to easily switch between the versions.

	require_once 'libAllure/Template.php';

	use \libAllure\Template;

	$tpl = new Template('myTemplates');
	$tpl->display('myTemplate.tpl');
