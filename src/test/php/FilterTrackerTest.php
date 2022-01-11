<?php

require_once 'common.php';

require_once 'libAllure/FilterTracker.php';

use \libAllure\FilterTracker;
use \PHPUnit\Framework\TestCase;

class FilterTrackerTest extends TestCase {
	public function testConstruct() {
		$tracker = new FilterTracker();

		$this->assertNotNull($tracker);
	}

	public function testBooleans() {
		$tracker = new FilterTracker();
		$tracker->addBool('test1', 'Test 1');

		$_REQUEST['test1'] = 'asdf';

		$this->assertTrue($tracker->isUsed('test1'));
		$this->assertTrue($tracker->getValue('test1'));
	}

	public function testSelect() {
		$options = array(
			array(
				'identifier' => 'bananas',
				'quantitiy' => 1.4,
			),
			array(
				'identifier' => 'apples',
				'quantity' => 30
			),
			array(
				'identifier' => 'pears',
				'quantity' => 3
			)
		);

		$tracker = new FilterTracker();
		$tracker->addSelect('testSelect', $options, 'identifier', 'Test Select');

		$_REQUEST['testSelect'] = 'apples';

		$this->assertTrue($tracker->isUsed('testSelect'));
		$this->assertEquals($tracker->getValue('testSelect'), 'apples');
	}
}

?>
