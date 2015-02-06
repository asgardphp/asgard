<?php
namespace Asgard\Orm\Tests;

class MigrationsTest extends \PHPUnit_Framework_TestCase {
	protected static $ormm;
	protected static $em;
	protected static $schema;
	protected static $db;

	public static function setUpBeforeClass() {
		\Asgard\File\FileSystem::delete(__DIR__.'/migrations/');
		static::$db = $db = new \Asgard\Db\DB([
			'driver' => 'sqlite',
			'database' => ':memory:',
		]);
		static::$em = $entityManager = new \Asgard\Entity\EntityManager;
		$dataMapper = new \Asgard\Orm\DataMapper($db, $entityManager);
		static::$schema = new \Asgard\Db\Schema($db);
		static::$ormm = new \Asgard\Orm\ORMMigrations($dataMapper, new \Asgard\Migration\MigrationManager(__DIR__.'/migrations/', $db, $db->getSchema()));
	}

	public function testAutoMigrate() {
		static::$schema->dropAll();
		static::$ormm->autoMigrate([
			static::$em->get('Asgard\Orm\Tests\Fixtures\Migrations\Post'),
			static::$em->get('Asgard\Orm\Tests\Fixtures\Migrations\Category'),
			static::$em->get('Asgard\Orm\Tests\Fixtures\Migrations\Author')
		], static::$schema);

		$tables = [];
		foreach(static::$db->getSchema()->listTables() as $table) {
			$table = $table->getName();
			$tables[$table] = static::$db->query('PRAGMA table_info(['.$table.']);')->all();
		}

		$this->assertEquals([
			'author' =>
			[
				0 =>
				[
					'cid' => '0',
					'name' => 'id',
					'type' => 'INTEGER',
					'notnull' => '1',
					'dflt_value' => NULL,
					'pk' => '1',
				],
				1 =>
				[
					'cid' => '1',
					'name' => 'name',
					'type' => 'VARCHAR(255)',
					'notnull' => '0',
					'dflt_value' => 'NULL',
					'pk' => '0',
				],
			],
			'category' =>
			[
				0 =>
				[
					'cid' => '0',
					'name' => 'id',
					'type' => 'INTEGER',
					'notnull' => '1',
					'dflt_value' => NULL,
					'pk' => '1',
				],
				1 =>
				[
					'cid' => '1',
					'name' => 'name',
					'type' => 'VARCHAR(255)',
					'notnull' => '0',
					'dflt_value' => 'NULL',
					'pk' => '0',
				],
			],
			'category_post' =>
			[
				0 =>
				[
					'cid' => '0',
					'name' => 'categories_id',
					'type' => 'INTEGER',
					'notnull' => '0',
					'dflt_value' => 'NULL',
					'pk' => '0',
				],
				1 =>
				[
					'cid' => '1',
					'name' => 'posts_id',
					'type' => 'INTEGER',
					'notnull' => '0',
					'dflt_value' => 'NULL',
					'pk' => '0',
				],
			],
			'post' =>
			[
				0 =>
				[
					'cid' => '0',
					'name' => 'id',
					'type' => 'INTEGER',
					'notnull' => '1',
					'dflt_value' => NULL,
					'pk' => '1',
				],
				1 =>
				[
					'cid' => '1',
					'name' => 'title',
					'type' => 'VARCHAR(255)',
					'notnull' => '1',
					'dflt_value' => '\'a\'',
					'pk' => '0',
				],
				2 =>
				[
					'cid' => '2',
					'name' => 'posted',
					'type' => 'DATE',
					'notnull' => '0',
					'dflt_value' => 'NULL',
					'pk' => '0',
				],
				3 =>
				[
					'cid' => '3',
					'name' => 'author_id',
					'type' => 'INTEGER',
					'notnull' => '0',
					'dflt_value' => 'NULL',
					'pk' => '0',
				],
			],
			'post_translation' =>
			[
				0 =>
				[
					'cid' => '0',
					'name' => 'id',
					'type' => 'INTEGER',
					'notnull' => '1',
					'dflt_value' => NULL,
					'pk' => '0',
				],
				1 =>
				[
					'cid' => '1',
					'name' => 'locale',
					'type' => 'VARCHAR(50)',
					'notnull' => '1',
					'dflt_value' => NULL,
					'pk' => '0',
				],
				2 =>
				[
					'cid' => '2',
					'name' => 'content',
					'type' => 'CLOB',
					'notnull' => '0',
					'dflt_value' => 'NULL',
					'pk' => '0',
				],
			]],
			$tables
		);
	}

	public function testGenerateMigration() {
		static::$schema->dropAll();

		static::$ormm->autoMigrate([
			static::$em->get('Asgard\Orm\Tests\Fixtures\Migrations\Post'),
		], static::$schema);

		static::$ormm->generateMigration([
			static::$em->get('Asgard\Orm\Tests\Fixtures\Migrations\Post2'),
			static::$em->get('Asgard\Orm\Tests\Fixtures\Migrations\Author'),
			static::$em->get('Asgard\Orm\Tests\Fixtures\Migrations\Category')
		], 'Post');

		$this->assertRegExp('/\{'."\n".
'    "Post": \{'."\n".
'        "added": [0-9.]+'."\n".
'    \}'."\n".
'\}/', file_get_contents(__DIR__.'/migrations/migrations.json'));

		$this->assertEquals(self::lines(file_get_contents(__DIR__.'/Fixtures/Migrations/Post_2.php')), self::lines(file_get_contents(__DIR__.'/migrations/Post.php')));
	}

	private static function lines($s) {
		$s = str_replace("\r\n", "\n", $s);
		$s = str_replace("\r", "\n", $s);
		$s = preg_replace("/\n{2,}/", "\n\n", $s);
		return $s;
	}
}