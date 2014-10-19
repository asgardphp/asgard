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
			'host' => 'localhost',
			'user' => 'root',
			'password' => '',
			'database' => 'asgard'
		]);
		static::$em = $entityManager = new \Asgard\Entity\EntityManager;
		$dataMapper = new \Asgard\Orm\DataMapper($db, $entityManager);
		static::$schema = new \Asgard\Db\Schema($db);
		static::$ormm = new \Asgard\Orm\ORMMigrations($dataMapper, new \Asgard\Migration\MigrationManager(__DIR__.'/migrations/'));
	}

	public function testAutoMigrate() {
		static::$schema->dropAll();
		static::$ormm->autoMigrate([
			static::$em->get('Asgard\Orm\Tests\Fixtures\Migrations\Post'),
			static::$em->get('Asgard\Orm\Tests\Fixtures\Migrations\Category'),
			static::$em->get('Asgard\Orm\Tests\Fixtures\Migrations\Author')
		], static::$schema);

		$tables = [];
		foreach(static::$db->query('SHOW TABLES')->all() as $v) {
			$table = array_values($v)[0];
			$tables[$table] = static::$db->query('Describe `'.$table.'`')->all();
		}

		$this->assertEquals(
			[
				'author' => [
					[
						'Field' => 'id',
						'Type' => 'int(11)',
						'Null' => 'NO',
						'Key' => 'PRI',
						'Default' => NULL,
						'Extra' => 'auto_increment',
					],
					[
						'Field' => 'name',
						'Type' => 'varchar(255)',
						'Null' => 'YES',
						'Key' => '',
						'Default' => NULL,
						'Extra' => '',
					],
				],
				'category' => [
					[
						'Field' => 'id',
						'Type' => 'int(11)',
						'Null' => 'NO',
						'Key' => 'PRI',
						'Default' => NULL,
						'Extra' => 'auto_increment',
					],
					[
						'Field' => 'name',
						'Type' => 'varchar(255)',
						'Null' => 'YES',
						'Key' => '',
						'Default' => NULL,
						'Extra' => '',
					],
				],
				'post' => [
					[
						'Field' => 'id',
						'Type' => 'int(11)',
						'Null' => 'NO',
						'Key' => 'PRI',
						'Default' => NULL,
						'Extra' => 'auto_increment',
					],
					[
						'Field' => 'title',
						'Type' => 'varchar(255)',
						'Null' => 'NO',
						'Key' => 'UNI',
						'Default' => 'a',
						'Extra' => '',
					],
					[
						'Field' => 'posted',
						'Type' => 'date',
						'Null' => 'YES',
						'Key' => '',
						'Default' => NULL,
						'Extra' => '',
					],
					[
						'Field' => 'author_id',
						'Type' => 'int(11)',
						'Null' => 'YES',
						'Key' => '',
						'Default' => NULL,
						'Extra' => '',
					],
				],
				'post_translation' => [
					[
						'Field' => 'id',
						'Type' => 'int(11)',
						'Null' => 'NO',
						'Key' => '',
						'Default' => NULL,
						'Extra' => '',
					],
					[
						'Field' => 'locale',
						'Type' => 'varchar(50)',
						'Null' => 'NO',
						'Key' => '',
						'Default' => NULL,
						'Extra' => '',
					],
					[
						'Field' => 'content',
						'Type' => 'text',
						'Null' => 'YES',
						'Key' => '',
						'Default' => NULL,
						'Extra' => '',
					],
				],
				'category_post' => [
					[
						'Field' => 'categories_id',
						'Type' => 'int(11)',
						'Null' => 'YES',
						'Key' => '',
						'Default' => NULL,
						'Extra' => '',
					],
					[
						'Field' => 'posts_id',
						'Type' => 'int(11)',
						'Null' => 'YES',
						'Key' => '',
						'Default' => NULL,
						'Extra' => '',
					],
				],
			],
			$tables
		);
	}

	public function testGenerateMigration() {
		static::$schema->dropAll();
		static::$ormm->generateMigration([
			static::$em->get('Asgard\Orm\Tests\Fixtures\Migrations\Post'),
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