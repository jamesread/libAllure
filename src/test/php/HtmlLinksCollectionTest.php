<?php

require_once 'common.php';

require_once 'libAllure/HtmlLinksCollection.php';

use \libAllure\HtmlLinksCollection;

class HtmlLinksCollectionTest extends PHPUnit_Framework_TestCase {
	public function testCount() {
		$links = new HtmlLinksCollection();

		$this->assertEquals(count($links), 0);

		$links->add('foo', 'bar');

		$this->assertEquals(count($links), 1);
	}
}
