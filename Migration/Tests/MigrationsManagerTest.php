<?php
namespace Asgard\Migration\Tests;

class BrowserTest extends \PHPUnit_Framework_TestCase {
	public function testAddMigration() {
		\Asgard\Utils\FileManager::unlink(__DIR__.'/migrations');
		\Asgard\Utils\FileManager::copy(__DIR__.'/fixtures/migrations', __DIR__.'/migrations');
		$mm = new \Asgard\Migration\MigrationsManager(__DIR__.'/migrations/');
		$mm->add(__DIR__.'/fixtures/Migration.php');
		$this->assertTrue(file_exists(__DIR__.'/migrations/Migration.php'));
		$this->assertRegExp('/\{'."\n".
'    "Migration": \{'."\n".
'        "added": [0-9.]+'."\n".
'    \}'."\n".
'\}/', file_get_contents(__DIR__.'/migrations/migrations.json'));
	}

	public function testCreateAndRemoveMigration() {
		\Asgard\Utils\FileManager::unlink(__DIR__.'/migrations');
		\Asgard\Utils\FileManager::copy(__DIR__.'/fixtures/migrations', __DIR__.'/migrations');
		$mm = new \Asgard\Migration\MigrationsManager(__DIR__.'/migrations/');
		$mm->create('up();', 'down();', 'Amigration');
		$this->assertTrue(file_exists(__DIR__.'/migrations/Amigration.php'));
		$this->assertEquals('<?php'."\n".
'class Amigration extends \Asgard\Migration\Migration {'."\n".
'	public function up() {'."\n".
'		up();'."\n".
'	}'."\n".
'	'."\n".
'	public function down() {'."\n".
'		down();'."\n".
'	}'."\n".
'}', $this->normalize(file_get_contents(__DIR__.'/migrations/Amigration.php')));
		$this->assertRegExp('/\{'."\n".
'    "Amigration": \{'."\n".
'        "added": [0-9.]+'."\n".
'    \}'."\n".
'\}/', file_get_contents(__DIR__.'/migrations/migrations.json'));

		$mm->remove('AMigration');
		$this->assertRegExp('/\['."\n".
"\n".
'\]/', file_get_contents(__DIR__.'/migrations/migrations.json'));
	}

	public function testMigrate() {
		\Asgard\Utils\FileManager::unlink(__DIR__.'/migrations');
		\Asgard\Utils\FileManager::copy(__DIR__.'/fixtures/migrations_migrate', __DIR__.'/migrations');
		$mm = new \Asgard\Migration\MigrationsManager(__DIR__.'/migrations/');
		$mm->migrate('Migration', true);
		$this->assertTrue(\Migration::$migrated);

		$this->assertRegExp('/\{'."\n".
'    "Migration": \{'."\n".
'        "migrated": [0-9.]+'."\n".
'    \}'."\n".
'\}/', file_get_contents(__DIR__.'/migrations/tracking.json'));
	}

	public function testMigrateAll() {
		\Asgard\Utils\FileManager::unlink(__DIR__.'/migrations');
		\Asgard\Utils\FileManager::copy(__DIR__.'/fixtures/migrations_all', __DIR__.'/migrations');
		$mm = new \Asgard\Migration\MigrationsManager(__DIR__.'/migrations/');
		$mm->migrateAll(true);

		$this->assertTrue(\Migration1::$migrated);
		$this->assertTrue(\Migration2::$migrated);

		$this->assertRegExp('/\{'."\n".
'    "Migration1": \{'."\n".
'        "migrated": [0-9.]+'."\n".
'    \},'."\n".
'    "Migration2": \{'."\n".
'        "migrated": [0-9.]+'."\n".
'    \}'."\n".
'\}/', file_get_contents(__DIR__.'/migrations/tracking.json'));
	}

// 	public function testMigrateNext() {
// 		\Asgard\Utils\FileManager::unlink(__DIR__.'/migrations');
// 		\Asgard\Utils\FileManager::copy(__DIR__.'/fixtures/migrations_next', __DIR__.'/migrations');
// 		$mm = new \Asgard\Migration\MigrationsManager(__DIR__.'/migrations/');
// 		$mm->migrateNext();
// 		$this->assertTrue(\MigrationNext::$migrated);

// 		$this->assertRegExp('/\{'."\n".
// '    "Migration": \{'."\n".
// '        "added": [0-9.]+,'."\n".
// '        "migrated": [0-9.]+'."\n".
// '    \},'."\n".
// '    "MigrationNext": \{'."\n".
// '        "added": [0-9.]+,'."\n".
// '        "migrated": [0-9.]+'."\n".
// '    \}'."\n".
// '\}/', file_get_contents(__DIR__.'/migrations/migrations.json'));
// 	}

	public function testRollback() {
		\Asgard\Utils\FileManager::unlink(__DIR__.'/migrations');
		\Asgard\Utils\FileManager::copy(__DIR__.'/fixtures/migrations_last', __DIR__.'/migrations');
		$mm = new \Asgard\Migration\MigrationsManager(__DIR__.'/migrations/');
		$mm->rollback();
		$this->assertTrue(\MigrationLast::$unmigrated);
		$this->assertFalse(class_exists('MigrationDoNot') && \MigrationDoNot::$unmigrated);

		$this->assertRegExp('/\{'."\n".
'    "MigrationDoNot": \{'."\n".
'        "migrated": [0-9.]+'."\n".
'    \}'."\n".
'\}/', file_get_contents(__DIR__.'/migrations/tracking.json'));
	}

	public function testRollbackUntil() {
		\Asgard\Utils\FileManager::unlink(__DIR__.'/migrations');
		\Asgard\Utils\FileManager::copy(__DIR__.'/fixtures/migrations_until', __DIR__.'/migrations');
		$mm = new \Asgard\Migration\MigrationsManager(__DIR__.'/migrations/');
		$mm->rollbackUntil('MigrationUntil');
		$this->assertTrue(\MigrationUntil::$unmigrated);
		$this->assertTrue(\Migration4::$unmigrated);
		$this->assertFalse(class_exists('Migration3') && \Migration3::$unmigrated);

		$this->assertRegExp('/\{'."\n".
'    "Migration3": \{'."\n".
'        "migrated": [0-9.]+'."\n".
'    \}'."\n".
'\}/', file_get_contents(__DIR__.'/migrations/tracking.json'));
	}

	protected function normalize($s) {
	    $s = str_replace("\r\n", "\n", $s);
	    $s = str_replace("\r", "\n", $s);
	    $s = preg_replace("/\n{2,}/", "\n\n", $s);
	    return $s;
	}
}