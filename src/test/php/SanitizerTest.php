<?php

require_once 'common.php';

use \libAllure\Sanitizer;
use \PHPUnit\Framework\TestCase;

class SanitizerTest extends TestCase {
	private $sanitizer;

	protected function setUp(): void {
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

	public function testFilterUint() {
		$_REQUEST['test'] = '0';
		$this->assertEquals(0, $this->sanitizer->filterUint('test'));
	}

	public function testFilterEnum() {
		unset($_REQUEST['test']);
		$this->assertEquals('Bananas', $this->sanitizer->filterEnum('test', array('Apples', 'Bananas', 'Chestnuts'), 'Bananas'));

		$_REQUEST['test'] = 'Bananas';
		$this->assertEquals('Bananas', $this->sanitizer->filterEnum('test', array('Apples', 'Bananas', 'Chestnuts'), 'Bananas'));

		$_REQUEST['test'] = 'Waffles';
		$this->assertEquals('Bananas', $this->sanitizer->filterEnum('test', array('Apples', 'Bananas', 'Chestnuts'), 'Bananas'));
	}

	public function testFilterNumeric() {
		$this->assertNotNull($this->sanitizer->filterNumeric('0101'));
	}	
}

?>
