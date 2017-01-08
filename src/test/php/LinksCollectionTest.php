<?php 

require_once 'common.php';
require_once 'libAllure/HtmlLinksCollection.php';

use \libAllure\HtmlLinksCollection;

class LinksCollectionTest extends PHPUnit_Framework_TestCase {
	public function testAddLinks() {
		$links = new HtmlLinksCollection();
		$this->assertEquals(0, count($links->getAll()));

		$links->add('foo', 'bar');

		$this->assertEquals(1, count($links->getAll()));

		$link = $links->current();

		$this->assertEquals('foo', $link['url']);
		$this->assertEquals('bar', $link['title']);
		$this->assertNull($links->next());
	}

}

?>
