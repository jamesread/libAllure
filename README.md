libAllure
==

A set of utilities, helpers and shims. It aims to be pretty modular and lightweight.

This library is published by packagist.org for easy use with composer; https://packagist.org/packages/jwread/lib-allure 

[![PHP Composer](https://github.com/jamesread/libAllure/actions/workflows/php.yml/badge.svg)](https://github.com/jamesread/libAllure/actions/workflows/php.yml)

**API Documentation**: http://jamesread.github.io/libAllure/

## Compatibiility

|               | Up to PHP 5.5.x | Up tp PHP 7.3 | PHP 8         |
| ------------- | --------------- | ------------- | ------------- |
| libAllure 1.x | supported       | not supported | not supported |
| libAllure 2.x | not supported   | supported     | not supported |
| libAllure 8.x | not supported   | not supported | supported     |

## Adding with `composer`

You can add libAllure to your project quickly, if you're using composer.

	composer require jwread/lib-allure

Then to use it, like in test.php;

```php
<?php

require_once 'vendor/autoload.php';

use \libAllure\Database;
use \libAllure\ErrorHandler;
use \libAllure\Form;

// etc

?>
```

## Adding with a standard PHP include

Copy the contents of `/src/main/php/` to somewhere on your include path, like 
`/usr/share/php/` on most Linux distributions. So that you have `/usr/share/php/libAllure/ErrorHander.php`, `/usr/share/php/libAllure/Database.php`, etc.

## API

### Database
Wrapper around **PDO**.

```php
require_once 'libAllure/Database.php';

use \libAllure\Database;

$database = new Database('mysql:dbname=testdb;host=127.0.0.1', 'username', 'password');

$sql = 'SELECT p.id, p.title FROM products p';
$results = $database->prepare($sql)->execute();

var_dump($results->fetchAll());
```

### ErrorHandler
Custom error handler that complains at the slightest thing, makes debugging nice and easy.

```php
require_once 'libAllure/ErrorHandler.php';

use \libAllure\ErrorHandler;

$handler = new ErrorHandler();
$handler->beGreedy();

throw new Exception('This is a test');
```

### Form
Custom form handling code. 

```php
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
```

### Template
Just a nice wrapper around Smarty2/3, that adds in a few compatibility functions to easily switch between the versions.

```php
use \libAllure\Template;

$tpl = new Template('myTemplates');
$tpl->display('myTemplate.tpl');
```
