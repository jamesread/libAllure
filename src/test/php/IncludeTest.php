<?php

require_once 'common.php';

require_once 'libAllure/IncludePath.php';

use \libAllure\IncludePath;
use \PHPUnit\Framework\TestCase;

class IncludePathTest extends TestCase {
	public function testAdd() {
		$this->assertFalse(stripos(get_include_path(), 'foo'));

		IncludePath::add('foo');

		$this->assertNotFalse(stripos(get_include_path(), 'foo'));
	}
}

?>
