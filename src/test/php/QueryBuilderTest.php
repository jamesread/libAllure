<?php

require_once 'common.php';
require_once 'libAllure/QueryBuilder.php';

use libAllure\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    public function testSelect() {
        $qb = new QueryBuilder('select');
        $qb->from('users');
        $qb->fields('username', 'password');

        $this->assertEquals('SELECT u.username, u.password FROM users u ORDER BY u.username', $qb->build());
    }

    public function testSelectDifferentDatabase()
    {
        $qb = new QueryBuilder();
        $qb->from('users', null, 'otherDatabase');
        $qb->fields('username');

        $this->assertEquals('SELECT u.username FROM otherDatabase.users u ORDER BY u.username', $qb->build());
    }

    public function testSelectOrderId() {
        $qb = new QueryBuilder();
        $qb->from('users')->fields('u.username');
        $qb->orderBy('id');

        $this->assertEquals('SELECT u.username FROM users u ORDER BY u.id', $qb->build());
    }

    public function testAutoPrefixFields() {
        $qb = new QueryBuilder();
        $qb->from('users')->fields('id', 'forename');
        $qb->fields('surname');
        $qb->orderBy('id');

        $this->assertEquals('SELECT u.id, u.forename, u.surname FROM users u ORDER BY u.id', $qb->build());
    }

    public function testMultipleFilters() {
        $qb = new QueryBuilder();
        $qb->from('users');
        $qb->fields('forename', 'surname');
        $qb->whereGt('age', 25);
        $qb->whereEqualsValue('forename', 'bob');
        $qb->whereEqualsValue('surname', 'exampleton');
        $qb->whereNotNull('wallet');
        $qb->orderBy('id');

        $this->assertEquals('SELECT u.forename, u.surname FROM users u WHERE u.age > 25 AND u.forename = "bob" AND u.surname = "exampleton" AND u.wallet NOT NULL ORDER BY u.id', $qb->build());
    }

    public function testJoin() {
        $qb = new QueryBuilder();
        $qb->from('users')->fields('email', array('count(o.id)', 'orderCount'));
        $qb->leftJoin('orders')->onEq('o.uid', 'u.id');
        $qb->orderBy('orderCount');

        $this->assertEquals('SELECT u.email, count(o.id) AS orderCount FROM users u LEFT JOIN orders o ON o.uid = u.id ORDER BY u.orderCount', $qb->build());
    }

    public function testMultiJoin() {
        $qb = new QueryBuilder();
        $qb->from('users')->fields('email', array('count(o.id)', 'orderCount'), 'forename', 'surname');
        $qb->leftJoin('orders')->onEq('o.uid', 'u.id');
        $qb->leftJoin('groups')->onEq('u.group', 'g.id')->fields('title');
        $qb->joinedTable('orders')->onGt('o.date', ':date');
        $qb->orderBy('email');

        $this->assertEquals('SELECT u.email, count(o.id) AS orderCount, u.forename, u.surname, g.title FROM users u LEFT JOIN orders o ON o.uid = u.id AND o.date > :date LEFT JOIN groups g ON u.group = g.id ORDER BY u.email', $qb->build());
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

    public function testGroup() {
        $qb = new QueryBuilder();
        $qb->fields('p.id', 'p.forename')->from('people');
        $qb->groupBy('forename');

        $this->assertEquals('SELECT p.id, p.forename FROM people p GROUP BY p.forename ORDER BY p.id', $qb->build());
    }

    public function testJoinAlias() 
    {
        $qb = new QueryBuilder();
        $qb->from('activity_tracker', 'a', 'dw')->fields('*');
        $qb->groupBy('id');
        $qb->join('activity_types', 't', 'dw')->onEq('a.type', 't.id')->fields(['title', 'type_fk_description']);
        $qb->orderBy('id DESC');

        $sql = 'SELECT a.*, t.title AS type_fk_description FROM dw.activity_tracker a LEFT JOIN dw.activity_types t ON a.type = t.id GROUP BY a.id ORDER BY a.id DESC';
        $this->assertEquals($sql, $qb->build());
    }

    public function testAutoAlias() {
        $qb = new QueryBuilder();
        $qb->from('foo')->fields('username');
        $qb->join('fffoobar')->onEq('f.username', 'o.id')->fields('password');

        $sql = 'SELECT f.username, o.password FROM foo f LEFT JOIN fffoobar o ON f.username = o.id ORDER BY f.username';
        $this->assertEquals($sql, $qb->build());
    }

    public function testJoinOnWhenDontKnowTableAliases() {
        $qb = new QueryBuilder();
        $qb->from('foo')->fields('username');
        $qb->join('fffoobar')->onFromFieldsEq('username', 'id')->fields('password');

        $sql = 'SELECT f.username, o.password FROM foo f LEFT JOIN fffoobar o ON f.username = o.id ORDER BY f.username';
        $this->assertEquals($sql, $qb->build());

    }
}


?>
