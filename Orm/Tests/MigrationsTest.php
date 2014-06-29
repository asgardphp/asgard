<?php
namespace Asgard\Orm\Tests;

class MigrationsTest extends \PHPUnit_Framework_TestCase {
	public function testAutoMigrate() {
		$app = new \Asgard\Container\Container;
		$app['db'] = new \Asgard\Db\DB([
			'host' => 'localhost',
			'user' => 'root',
			'password' => '',
			'database' => 'asgard'
		]);
		$app['config'] = new \Asgard\Config\Config;
		$app['hooks'] = new \Asgard\Hook\HooksManager;
		$app['cache'] = new \Asgard\Cache\NullCache;
		$app['entitiesManager'] = new \Asgard\Entity\EntitiesManager($app);
		\Asgard\Entity\Entity::setApp($app);

		$ormm = new \Asgard\Orm\ORMMigrations();
		$schema = new \Asgard\Db\Schema($app['db']);
		$schema->dropAll();

		$ormm->autoMigrate(['Asgard\Orm\Tests\Fixtures\Post', 'Asgard\Orm\Tests\Fixtures\Category', 'Asgard\Orm\Tests\Fixtures\Author'], $schema);

		$tables = [];
		foreach($app['db']->query('SHOW TABLES')->all() as $v) {
			$table = array_values($v)[0];
			$tables[$table] = $app['db']->query('Describe `'.$table.'`')->all();
		}

		$this->assertEquals(
			[
			  'author' =>
			  [
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
			  'category' =>
			  [
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
			  'post' =>
			  [
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
			  'post_translation' =>
			  [
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
			  'category_post' =>
			  [
			    [
			      'Field' => 'post_id',
			      'Type' => 'int(11)',
			      'Null' => 'NO',
			      'Key' => '',
			      'Default' => NULL,
			      'Extra' => '',
			    ],
			    [
			      'Field' => 'category_id',
			      'Type' => 'int(11)',
			      'Null' => 'NO',
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
		\Asgard\File\FileSystem::delete(__DIR__.'/migrations/');
		$db = new \Asgard\Db\DB([
			'host' => 'localhost',
			'user' => 'root',
			'password' => '',
			'database' => 'asgard'
		]);
		$schema = new \Asgard\Db\Schema($db);
		$schema->dropAll();

		$ormm = new \Asgard\Orm\ORMMigrations(new \Asgard\Migration\MigrationsManager(__DIR__.'/migrations/'));
		$ormm->generateMigration(['Asgard\Orm\Tests\Fixtures\Post', 'Asgard\Orm\Tests\Fixtures\Author', 'Asgard\Orm\Tests\Fixtures\Category'], 'Post', $db);

		$this->assertRegExp('/\{'."\n".
'    "Post": \{'."\n".
'        "added": [0-9.]+'."\n".
'    \}'."\n".
'\}/', file_get_contents(__DIR__.'/migrations/migrations.json'));

		$this->assertEquals(self::lines(file_get_contents(__DIR__.'/Fixtures/migrations/Post.php')), self::lines(file_get_contents(__DIR__.'/migrations/Post.php')));
	}

	private static function lines($s) {
		$s = str_replace("\r\n", "\n", $s);
		$s = str_replace("\r", "\n", $s);
		$s = preg_replace("/\n{2,}/", "\n\n", $s);
		return $s;
	}
}