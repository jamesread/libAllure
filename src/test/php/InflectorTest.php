<?php

require_once 'common.php';
require_once 'libAllure/Inflector.php';

use \libAllure\Inflector;
use \PHPUnit\Framework\TestCase;

class InflectorTest extends TestCase {
	public function testSingulars() {
		$this->assertEquals('muppet', Inflector::singular('muppets'));
		$this->assertEquals('cake', Inflector::singular('cakes'));
		$this->assertEquals('pansy', Inflector::singular('pansies'));
	}

	public function testPlurals() {
		$this->assertEquals('muppets', Inflector::plural('muppet'));
		$this->assertEquals('cakes', Inflector::plural('cake'));
		$this->assertEquals('pansies', Inflector::plural('pansy'));
	}

	public function testCamelCase() {
		$this->assertEquals('thisIsTheTest', Inflector::camelize('this is the test'));
		$this->assertEquals('libAllure', Inflector::camelize('lib allure'));
	}

	public function testHumanize() {
		$this->assertEquals('This Is The Test', Inflector::humanize('this is the test'));
		$this->assertEquals('Lib Allure', Inflector::humanize('Lib Allure'));
	}

	public function testUnderscore() {
		$this->assertEquals('this_is_the_test', Inflector::underscore('this is the test'));
		$this->assertEquals('this_is_the_test', Inflector::underscore('thisIsTheTest'));
	}

	public function testSes() {
		$this->assertEquals('bus', Inflector::singular('buses'));
	}

	public function testQuantities() {
		$this->assertEquals('cake', Inflector::quantify('cakes', 1));
		$this->assertEquals('cakes', Inflector::quantify('cake', 10));
	}

	public function testForcePlural() {
		$this->assertEquals('buses', Inflector::pluralize('bus', true));
	}
}

?>
