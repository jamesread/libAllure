<?php

require_once 'common.php';
require_once 'libAllure/QueryBuilder.php';

use \libAllure\QueryBuilder;

class QueryBuilderTest extends PHPUnit_Framework_TestCase {
	public function testSelect() {
		$qb = new QueryBuilder('select');
		$qb->fields('u.username', 'u.password');
		$qb->from('users');
		
		$this->assertEquals('SELECT u.username, u.password FROM users u ORDER BY u.username', $qb->build());
	}

	public function testSelectOrderId() {
		$qb = new QueryBuilder();
		$qb->from('users')->fields('u.username');
		$qb->orderBy('u.id');

		$this->assertEquals('SELECT u.username FROM users u ORDER BY u.id', $qb->build());
	}

	public function testAutoPrefixFields() {
		$qb = new QueryBuilder();
		$qb->from('users')->fields('id', 'forename');
		$qb->from('users')->fields('surname');
		$qb->orderBy('id');

		$this->assertEquals('SELECT u.id, u.forename, u.surname FROM users u ORDER BY u.id', $qb->build());
	}

	public function testMultipleFilters() {
		$qb = new QueryBuilder();
		$qb->from('users');
		$qb->fields('forename', 'surname');
		$qb->whereGt('age', 25);
		$qb->whereEquals('forename', 'bob');
		$qb->whereEquals('surname', 'exampleton');
		$qb->whereNotNull('wallet');
		$qb->orderBy('id');

		$this->assertEquals('SELECT u.forename, u.surname FROM users u WHERE u.age > 25 AND u.forename = "bob" AND u.surname = "exampleton" AND u.wallet NOT NULL ORDER BY u.id', $qb->build());
	}

	public function testJoin() {
		$qb = new QueryBuilder();
		$qb->from('users')->fields('email', array('count(o.id)', 'orderCount'));
		$qb->leftJoin('orders')->on('o.uid', 'u.id');
		$qb->orderBy('!orderCount');

		$this->assertEquals('SELECT u.email, count(o.id) AS orderCount FROM users u LEFT JOIN orders o ON o.uid = u.id ORDER BY orderCount', $qb->build());
	}

	public function testMultiJoin() {
		$qb = new QueryBuilder();
		$qb->from('users')->fields('email', array('count(o.id)', 'orderCount'), 'g.title');
		$qb->leftJoin('orders')->on('o.uid', 'u.id');
		$qb->leftJoin('groups')->on('u.group', 'g.id');
		$qb->joinedTable('orders')->onGt('o.date', ':date');
		$qb->fields('forename', 'surname');
		$qb->orderBy('email');

		$this->assertEquals('SELECT u.email, count(o.id) AS orderCount, g.title, u.forename, u.surname FROM users u LEFT JOIN orders o ON o.uid = u.id AND o.date > :date LEFT JOIN groups g ON u.group = g.id ORDER BY u.email', $qb->build());
	}

	public function testWhereNot() {
		$qb = new QueryBuilder();
		$qb->from('staff')->fields('id', 'forename', 'surname');
		$qb->whereNotEquals('karma', 'good');

		$this->assertEquals('SELECT s.id, s.forename, s.surname FROM staff s WHERE s.karma != "good" ORDER BY s.id', $qb->build());
	}

	public function testSubquery() {
		$qb = new QueryBuilder();
		$qb->from('staff');
		$qb->fields('id', 'forename');
		$qbInner = new QueryBuilder();
		$qbInner->from('staff', 's2')->fields('id');
		$qbInner->whereGt('s2.id', 500);
		$qb->whereSubquery('s.id', 'NOT IN', $qbInner);

		$this->assertEquals('SELECT s.id, s.forename FROM staff s WHERE s.id NOT IN (SELECT s2.id FROM staff s2 WHERE s2.id > 500 ORDER BY s2.id) ORDER BY s.id', $qb->build());
	}
}

?>
