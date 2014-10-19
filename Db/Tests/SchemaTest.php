<?php
namespace Asgard\Db\Tests;

class SchemaTest extends \PHPUnit_Framework_TestCase {
	protected $db;

	public static function setUpBeforeClass() {
		if(!defined('_ENV_'))
			define('_ENV_', 'test');
	}

	public function setUp(){
		$db = $this->getDB();
		$database = 'asgard';
		try {
			$db->query('DROP DATABASE `'.$database.'`');
		} catch(\Exception $e) {}
		$db->query('CREATE DATABASE `'.$database.'`');
		$db->query('USE `'.$database.'`');
	}

	protected function getDB() {
		if(!$this->db) {
			$this->db = new \Asgard\Db\DB([
				'host' => 'localhost',
				'user' => 'root',
				'password' => '',
				'database' => 'asgard',
			]);
		}
		return $this->db;
	}

	/*
	create new table
	drop table
	change table name
	indexes
		primary: unique or composite
		unique
		fulltext
		index
	drop index
	#foreign keys

	add column
	definition:
		autoincrement
		type: varchar, text, integer, ..
		length:
		nullable
		default
	rename column
	drop column

	raw sql
	*/

	protected function tableExists($table) {
		return $this->getDB()->query("SELECT *
			FROM INFORMATION_SCHEMA.TABLES
			WHERE TABLE_SCHEMA = ''
			AND  TABLE_NAME = '$table'")->count() > 0;
	}

	protected function columnExists($table, $column) {
		return $this->getDB()->query("SELECT *
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = 'asgard'
			AND  TABLE_NAME = '$table'
			AND COLUMN_NAME = '$column'")->count() > 0;
	}

	protected function isAutoincrement($table, $column) {
		return $this->getDB()->query("SELECT *
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = 'asgard'
			AND  TABLE_NAME = '$table'
			AND COLUMN_NAME = '$column'
			AND EXTRA LIKE '%auto_increment%'")->count() > 0;
	}

	protected function isNullable($table, $column) {
		return $this->getDB()->query("SELECT *
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = 'asgard'
			AND  TABLE_NAME = '$table'
			AND COLUMN_NAME = '$column'
			AND IS_NULLABLE = 'YES'")->count() > 0;
	}

	protected function getDefault($table, $column) {
		$r = $this->getDB()->query("SELECT *
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = 'asgard'
			AND  TABLE_NAME = '$table'
			AND COLUMN_NAME = '$column'")->first();
		return $r['COLUMN_DEFAULT'];
	}

	protected function getDataType($table, $column) {
		$r = $this->getDB()->query("SELECT *
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = 'asgard'
			AND  TABLE_NAME = '$table'
			AND COLUMN_NAME = '$column'")->first();
		return $r['DATA_TYPE'];
	}

	protected function getType($table, $column) {
		$r = $this->getDB()->query("SELECT *
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = 'asgard'
			AND  TABLE_NAME = '$table'
			AND COLUMN_NAME = '$column'")->first();
		return $r['COLUMN_TYPE'];
	}

	protected function getLength($table, $column) {
		$r = $this->getDB()->query("SELECT *
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = 'asgard'
			AND  TABLE_NAME = '$table'
			AND COLUMN_NAME = '$column'")->first();
		return $r['CHARACTER_MAXIMUM_LENGTH'];
	}

	protected function isPrimary($table, $column) {
		return $this->getDB()->query("SELECT *
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = 'asgard'
			AND  TABLE_NAME = '$table'
			AND COLUMN_NAME = '$column'
			AND COLUMN_KEY = 'PRI'")->count() > 0;
	}

	protected function isUnique($table, $column) {
		return $this->getDB()->query("SELECT *
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = 'asgard'
			AND  TABLE_NAME = '$table'
			AND COLUMN_NAME = '$column'
			AND COLUMN_KEY = 'UNI'")->count() > 0;
	}

	protected function isIndex($table, $column) {
		return $this->getDB()->query("SELECT *
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = 'asgard'
			AND  TABLE_NAME = '$table'
			AND COLUMN_NAME = '$column'
			AND COLUMN_KEY = 'MUL'")->count() > 0;
	}

	//~ isFulltext()
//~ select group_concat(distinct column_name)
//~ from information_schema.STATISTICS
//~ where table_schema = 'your_db'
//~ and table_name = 'your_table'
//~ and index_type = 'FULLTEXT';

	#create table
	//~ public function test1() {
		//~ $this->assertTrue($this->tableExists('arpa_actualite'));
		//~ $this->assertTrue($this->columnExists('arpa_actualite', 'id'));
	//~ }

	#nullable
	//~ ALTER TABLE mytable MODIFY mycolumn VARCHAR(255);
	#primary
	#index
	#unique
	#set type
	#rename
	#drop
	#add
	#create table
//~ CREATE TABLE `arpa_actualite` (
  //~ `id` int(11) NOT NULL AUTO_INCREMENT,
  //~ `titre` text,
  //~ `date` text,
  //~ `lieu` text,
  //~ `introduction` text,
  //~ `contenu` text,
  //~ `slug` text,
  //~ `position` int(11) NOT NULL,
  //~ `created_at` datetime NOT NULL,
  //~ `updated_at` datetime NOT NULL,
  //~ `filename_image` text,
  //~ `commentaire_id` int(1) NOT NULL,
  //~ PRIMARY KEY (`id`)
//~ );

	public function test0() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->add('id', 'int', 11)
				->primary()
				->autoincrement();
			$table->add('title', 'varchar', 50)
				->unique()
				->nullable()
				->def('The title');
		});
	}

	public function test1() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->add('id', 'int', 11);
		});

		$schema->drop('test');
	}

	public function test2() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->add('id', 'int', 11);
		});

		$schema->rename('test', 'test2');
	}

	public function test3() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->add('id', 'int', 11);
		});

		$schema->table('test', function($table) {
			$table->add('title', 'text');
		});
	}

	public function test4() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->add('id', 'int', 11);
			$table->add('title', 'text');
		});

		$schema->dropColumn('test', 'title');
	}

	public function test5() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->add('id', 'int', 11);
			$table->add('title', 'text');
		});

		$schema->renameColumn('test', 'title', 'title2');
	}

	public function test6() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->add('id', 'int', 11);
			$table->add('title', 'text');
		});

		$schema->table('test', function($table) {
			$table->col('title')
				->type('varchar', 50)
				->rename('title2')
				->nullable()
				->notNullable()
				->def('the title');
		});
	}

	public function test7() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->add('id', 'int', 11);
			$table->add('title', 'varchar', 50);
		});

		$schema->table('test', function($table) {
			$table->col('title')
				->dropIndex()
				->unique();
		});
	}

	public function test8() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->add('id', 'int', 11);
			$table->add('title', 'varchar', 50);
		});

		$schema->table('test', function($table) {
			$table->col('title')
				->dropIndex()
				->index();
		});
	}

	public function test9() {
		$schema = new \Asgard\Db\Schema($this->getDB());
		$schema->create('test', function($table) {
			$table->add('id', 'int', 11);
			$table->add('title', 'varchar', 50);
		});

		$schema->table('test', function($table) {
			$table->primary(['columns' => ['id', 'title']]);
		});
	}
}
