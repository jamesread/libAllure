<?php

require_once 'common.php';
require_once 'libAllure/Sanitizer.php';

use \libAllure\Sanitizer;

class SanitizerTest extends PHPUnit_Framework_TestCase {
	private $sanitizer;

	public function __construct() {
		$this->sanitizer = Sanitizer::getInstance();
	}

	public function testFormatBool() {
		$this->assertTrue($this->sanitizer->formatBool(1));
		$this->assertTrue($this->sanitizer->formatBool(true));
		$this->assertTrue($this->sanitizer->formatBool('true'));
		$this->assertTrue($this->sanitizer->formatBool('false'));
		$this->assertFalse($this->sanitizer->formatBool(false));
	}

	public function testFormatNumericAsHex() {
		$this->assertEquals('a', $this->sanitizer->formatNumericAsHex(10));
		$this->assertEquals('a', $this->sanitizer->formatNumericAsHex(0xA));
		$this->assertEquals('cafe', $this->sanitizer->formatNumericAsHex(0xCAFE));
	}
}

?>
