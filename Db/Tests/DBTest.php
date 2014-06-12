<?php
namespace Asgard\Db\Tests;

class DBTest extends \PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');
	}

	public function test1() {
		$config = [
			'host' => 'localhost',
			'user' => 'root',
			'password' => '',
			'database' => 'asgard',
		];
		$db = new \Asgard\Db\DB($config);
		$db2 = new \Asgard\Db\DB($config);
		$mysql = new \Asgard\Db\MySQL($config);

		$this->assertTrue($db->getDB() instanceof \PDO);

		$mysql->import(__dir__.'/sql/test1.sql');
		$db->query('SELECT title FROM news WHERE id=?', [1]);

		$this->assertEquals('The first news!', $db->query('SELECT title FROM news')->first()['title']);

		$db->query('INSERT INTO news (title) VALUES (?)', ['Another news!']);
		$this->assertEquals(2, $db->id());

		$rows = [
			[
				'id' => '1',
				'title' => 'The first news!',
			],
			[
				'id' => '2',
				'title' => 'Another news!',
			]
		];
		$this->assertEquals(
			$rows,
			$db->query('SELECT * FROM news')->all()
		);
		$i = 0;
		$q = $db->query('SELECT * FROM news ORDER BY id ASC');
		while($row = $q->next()) {
			$this->assertEquals($rows[$i++], $row);
		}

		$db->beginTransaction();
		$db->query('INSERT INTO news (title) VALUES (?)', ['Another news!']);
		$this->assertEquals(2, $db2->query('SELECT * FROM news ORDER BY id DESC')->first()['id']);
		$db->commit();
		$this->assertEquals(3, $db2->query('SELECT * FROM news ORDER BY id DESC')->first()['id']);

		$db->beginTransaction();
		$db->query('INSERT INTO news (title) VALUES (?)', ['Another news!']);
		$this->assertEquals(3, $db2->query('SELECT * FROM news ORDER BY id DESC')->first()['id']);
		$db->rollback();
		$this->assertEquals(3, $db->query('SELECT * FROM news ORDER BY id DESC')->first()['id']);

		$this->assertEquals(3, $db->query('SELECT * FROM news')->count());

		$this->assertEquals(5, $db->query('INSERT INTO news (title) VALUES (?)', ['Another news!'])->id());

		$this->assertEquals(4, $db->query('UPDATE news SET title = ?', ['test'])->affected());
	}
}