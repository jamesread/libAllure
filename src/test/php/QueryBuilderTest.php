<?php

require_once 'common.php';

use \libAllure\QueryBuilder;

class QueryBuilderTest extends PHPUnit_Framework_TestCase {
	public function testSelect() {
		$qb = new QueryBuilder('select');
		$qb->fields('u.username', 'u.password');
		$qb->from('users u');
		
		$this->assertEquals('SELECT u.username, u.password FROM users u ORDER BY u.username', $qb->build());
	}

	public function testSelectOrderId() {
		$qb = new QueryBuilder();
		$qb->from('users u')->fields('u.username');
		$qb->orderBy('u.id');

		$this->assertEquals('SELECT u.username FROM users u ORDER BY u.id', $qb->build());
	}
}

?>
