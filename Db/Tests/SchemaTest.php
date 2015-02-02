<?php
namespace Asgard\Db\Tests;

class SchemaTest extends \PHPUnit_Framework_TestCase {
	protected $db;

	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');
	}

	public function setUp() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->dropAll();
	}

	protected function getDB() {
		if(!$this->db) {
			$this->db = new \Asgard\Db\DB([
				'driver' => 'sqlite',
				'database' => ':memory:',
			]);
		}
		return $this->db;
	}

	public function test0() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->addColumn('id', 'integer', [
				'length' => 11,
				'autoincrement' => true
			]);
			$table->addColumn('title', 'string', [
				'length' => 50,
				'notnull' => false,
				'default' => 'the title'
			]);
			
			$table->setPrimaryKey(['id']);
		});
	}

	public function test1() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->addColumn('id', 'integer', [
				'length' => 11,
			]);
			
		});

		$schema->drop('test');
	}

	public function test2() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->addColumn('id', 'integer', [
				'length' => 11,
			]);
			
		});

		$schema->rename('test', 'test2');
	}

	public function test3() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->addColumn('id', 'integer', [
				'length' => 11,
			]);
			
		});

		$schema->table('test', function($table) {
			$table->addColumn('title', 'text');
		});
	}

	public function test4() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->addColumn('id', 'integer', [
				'length' => 11,
			]);
			$table->addColumn('title', 'text');
		});

		$schema->table('test', function($table) {
			$table->dropColumn('title');
		});
	}

	public function test5() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->addColumn('id', 'integer', [
				'length' => 11,
			]);
			$table->addColumn('title', 'text');

			$table->rename('title', 'text');
		});
	}

	public function test6() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->addColumn('id', 'integer', [
				'length' => 11,
			]);
			$table->addColumn('title', 'text');
			
		});

		$schema->table('test', function($table) {
			$table->changeColumn('title', [
				'type' => 'string',
				'length' => 50,
				'name' => 'title2',
				'notnull' => true,
				'default' => 'the title',
			]);
			
		});
	}

	public function test7() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->addColumn('id', 'integer', [
				'length' => 11,
			]);
			$table->addColumn('title', 'string', [
				'length' => 50,
			]);
			
		});

		$schema->table('test', function($table) {
			$table->addUniqueIndex(['title'], 'title');
			
		});
	}

	public function test8() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->addColumn('id', 'integer', [
				'length' => 11,
			]);
			$table->addColumn('title', 'string', [
				'length' => 50,
			]);
			
		});

		$schema->table('test', function($table) {
			$table->addUniqueIndex(['title'], 'title');
			
		});
	}

	public function test9() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->addColumn('id', 'integer', [
				'length' => 11,
			]);
			$table->addColumn('title', 'string', [
				'length' => 50,
			]);
		});

		$schema->table('test', function($table) {
			$table->setPrimaryKey(['id', 'title']);
			
		});
	}
}
