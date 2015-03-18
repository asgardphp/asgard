<?php
namespace Asgard\Migration\Tests;

class BrowserTest extends \PHPUnit_Framework_TestCase {
	protected $mm;
	protected $db;

	protected function getMigrationManager() {
		if(!$this->mm) {
				$this->db = $db = new \Asgard\Db\DB([
				'driver' => 'sqlite',
				'database' => ':memory:',
			]);
			$schema = $db->getSchema();
			$this->mm = new \Asgard\Migration\MigrationManager(__DIR__.'/migrations/', $db, $schema);
		}
		$this->mm->getTracker()->createTable();
		$this->db->dal()->from('_migrations')->delete();
		return $this->mm;
	}

	public function testAddMigration() {
		\Asgard\File\FileSystem::delete(__DIR__.'/migrations');
		\Asgard\File\FileSystem::copy(__DIR__.'/fixtures/migrations', __DIR__.'/migrations');
		$mm = $this->getMigrationManager();
		$mm->add(__DIR__.'/fixtures/Migration.php');
		$this->assertTrue(file_exists(__DIR__.'/migrations/Migration.php'));
		$this->assertRegExp('/\{'."\n".
'    "Migration": \{'."\n".
'        "added": [0-9.]+'."\n".
'    \}'."\n".
'\}/', file_get_contents(__DIR__.'/migrations/migrations.json'));
	}

	public function testCreateAndRemoveMigration() {
		\Asgard\File\FileSystem::delete(__DIR__.'/migrations');
		\Asgard\File\FileSystem::copy(__DIR__.'/fixtures/migrations', __DIR__.'/migrations');
		$mm = $this->getMigrationManager();
		$mm->create('up();', 'down();', 'Amigration');
		$this->assertEquals(1, count(glob(__DIR__.'/migrations/Amigration_*.php')));

		$c = $this->normalize(file_get_contents(glob(__DIR__.'/migrations/Amigration_*.php')[0]));
		$c2 = preg_replace('/Amigration_[0-9]+/', 'Amigration_123', $c);

		$this->assertEquals('<?php'."\n".
'class Amigration_123 extends \Asgard\Migration\Migration {'."\n".
'	public function up() {'."\n".
'		up();'."\n".
'	}'."\n".
''."\n".
'	public function down() {'."\n".
'		down();'."\n".
'	}'."\n".
'}', $c2);

		$this->assertRegExp('/\{'."\n".
'    "Amigration_[0-9]+": \{'."\n".
'        "added": [0-9.]+'."\n".
'    \}'."\n".
'\}/', file_get_contents(__DIR__.'/migrations/migrations.json'));

		preg_match('/Amigration_[0-9]+/', $c, $matches);
		$name = $matches[0];

		$mm->remove($name);
		$this->assertRegExp('/\[\s*\]/', file_get_contents(__DIR__.'/migrations/migrations.json'));
	}

	public function testMigrate() {
		\Asgard\File\FileSystem::delete(__DIR__.'/migrations');
		\Asgard\File\FileSystem::copy(__DIR__.'/fixtures/migrations_migrate', __DIR__.'/migrations');
		$mm = $this->getMigrationManager();
		$mm->migrate('Migration', true);
		$this->assertTrue(\Migration::$migrated);

		$this->assertRegExp('/\{'."\n".
'    "Migration": \{'."\n".
'        "added": [0-9.]+,'."\n".
'        "migrated": [0-9.]+'."\n".
'    \}'."\n".
'\}/', json_encode($mm->getTracker()->getList(), JSON_PRETTY_PRINT));
	}

	public function testMigrateAll() {
		\Asgard\File\FileSystem::delete(__DIR__.'/migrations');
		\Asgard\File\FileSystem::copy(__DIR__.'/fixtures/migrations_all', __DIR__.'/migrations');
		$mm = $this->getMigrationManager();
		$mm->migrateAll(true);

		$this->assertTrue(\Migration1::$migrated);
		$this->assertTrue(\Migration2::$migrated);

		$this->assertRegExp('/\{'."\n".
'    "Migration1": \{'."\n".
'        "added": [0-9.]+,'."\n".
'        "migrated": [0-9.]+'."\n".
'    \},'."\n".
'    "Migration2": \{'."\n".
'        "added": [0-9.]+,'."\n".
'        "migrated": [0-9.]+'."\n".
'    \}'."\n".
'\}/', json_encode($mm->getTracker()->getList(), JSON_PRETTY_PRINT));
	}

	public function testRollback() {
		\Asgard\File\FileSystem::delete(__DIR__.'/migrations');
		\Asgard\File\FileSystem::copy(__DIR__.'/fixtures/migrations_last', __DIR__.'/migrations');
		$mm = $this->getMigrationManager();
		$this->db->dal()->into('_migrations')->insert(['name'=>'MigrationDoNot', 'migrated'=>'2000-01-01 01:00:00']);
		$this->db->dal()->into('_migrations')->insert(['name'=>'MigrationLast', 'migrated'=>'2000-01-01 02:00:00']);
		
		$mm->rollback();
		$this->assertTrue(\MigrationLast::$unmigrated);
		$this->assertFalse(class_exists('MigrationDoNot') && \MigrationDoNot::$unmigrated);

		$this->assertRegExp('/\{'."\n".
'    "MigrationDoNot": \{'."\n".
'        "added": [0-9.]+,'."\n".
'        "migrated": [0-9.]+'."\n".
'    \},'."\n".
'    "MigrationLast": \{'."\n".
'        "added": [0-9.]+'."\n".
'    \}'."\n".
'\}/', json_encode($mm->getTracker()->getList(), JSON_PRETTY_PRINT));
	}

	public function testRollbackUntil() {
		\Asgard\File\FileSystem::delete(__DIR__.'/migrations');
		\Asgard\File\FileSystem::copy(__DIR__.'/fixtures/migrations_until', __DIR__.'/migrations');
		$mm = $this->getMigrationManager();
		$this->db->dal()->into('_migrations')->insert(['name'=>'Migration3', 'migrated'=>'2000-01-01 01:00:00']);
		$this->db->dal()->into('_migrations')->insert(['name'=>'MigrationUntil', 'migrated'=>'2000-01-01 02:00:00']);
		$this->db->dal()->into('_migrations')->insert(['name'=>'Migration4', 'migrated'=>'2000-01-01 03:00:00']);

		$mm->rollbackUntil('MigrationUntil');
		$this->assertTrue(\MigrationUntil::$unmigrated);
		$this->assertTrue(\Migration4::$unmigrated);
		$this->assertFalse(class_exists('Migration3') && \Migration3::$unmigrated);

		$this->assertRegExp('/\{'."\n".
'    "Migration3": \{'."\n".
'        "added": [0-9.]+,'."\n".
'        "migrated": [0-9.]+'."\n".
'    \},'."\n".
'    "MigrationUntil": \{'."\n".
'        "added": [0-9.]+'."\n".
'    \},'."\n".
'    "Migration4": \{'."\n".
'        "added": [0-9.]+'."\n".
'    \}'."\n".
'\}/', json_encode($mm->getTracker()->getList(), JSON_PRETTY_PRINT));
	}

	protected function normalize($s) {
		$s = str_replace("\r\n", "\n", $s);
		$s = str_replace("\r", "\n", $s);
		$s = preg_replace("/\n{2,}/", "\n\n", $s);
		return $s;
	}
}