<?php
namespace Asgard\Db\Tests;

class DBTest extends \PHPUnit_Framework_TestCase {
	public function test1() {
		$config = [
			'driver' => 'sqlite',
			'database' => tmpfile(),
		];
		$db = new \Asgard\Db\DB($config);
		$db2 = new \Asgard\Db\DB($config);

		$this->assertTrue($db->getPDO() instanceof \PDO);

		#Fixtures
		$db->getSchema()->drop('news');
		$db->getSchema()->create('news', function($table) {
			$table->addColumn('id', 'integer', [
					'length' => 4,
					'notnull' => true,
					'autoincrement' => true,
			]);
			$table->addColumn('title', 'string', [
					'length' => 255,
					'notnull' => true,
			]);


			$table->setPrimaryKey(['id']);
		});
		$db->dal()->into('news')->insert(['id'=>1, 'title'=>'The first news!']);

		#Tests
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
		while($row = $q->next())
			$this->assertEquals($rows[$i++], $row);

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

		$this->assertEquals(3, $db->query('UPDATE news SET title=1')->count());

		$db->query('INSERT INTO news (title) VALUES (?)', ['Another news!']);
		$this->assertEquals(4, $db->id());

		$this->assertEquals(4, $db->query('UPDATE news SET title = ?', ['test'])->affected());
	}
}