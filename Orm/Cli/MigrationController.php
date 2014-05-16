<?php
namespace Asgard\Orm\CLI;

class MigrationController extends \Asgard\Core\Cli\CLIController {
	/**
	@Shortcut('automigrate')
	@Usage('automigrate')
	@Description('Automacally build and process a migration from the Entities')
	*/
	public function automigrateAction($request) {
		\Asgard\Utils\FileManager::mkdir('migrations');
		echo 'Running diff..'."\n";
		$filename = \Asgard\Orm\MigrationsManager::diff('Diff', true);
		if(!$filename) {
			echo 'Nothing to migrate.';
			return;
		}
		echo 'New migration: '.$filename;

		\Asgard\Core\App::get('clirouter')->run('Asgard\Core\Cli\DBController', 'backup', $request);
		echo 'Migrating...'."\n";
		\Asgard\Orm\MigrationsManager::migrate($filename, true);
	}

	/**
	@Shortcut('diff')
	@Usage('diff')
	@Description('Automatically build a migration from the Entities')
	*/
	public function diffAction($request) {
		\Asgard\Utils\FileManager::mkdir('migrations');
		echo 'Running diff..'."\n";
	
		$diff = \Asgard\Orm\MigrationsManager::diff('Diff', true);
		if($diff)
			echo 'New migration: '.$diff;
		else
			echo 'The migration could not be generated automatically. You may have pending migrations.';
	}

	/**
	@Shortcut('migrate-next')
	@Usage('migrate-next')
	@Description('Automatically process migrations')
	*/
	public function migrateNextAction($request) {
		\Asgard\Core\App::get('clirouter')->run('Asgard\Core\Cli\DBController', 'backup', $request);
		echo 'Migrating...'."\n";

		\Asgard\Orm\MigrationsManager::migrateNext(true);
	}

	/**
	@Shortcut('unmigrate-last')
	@Usage('unmigrate-last')
	@Description('Automatically process migrations')
	*/
	public function unmigrateLastAction($request) {
		\Asgard\Core\App::get('clirouter')->run('Asgard\Core\Cli\DBController', 'backup', $request);
		echo 'Unmigrating...'."\n";

		\Asgard\Orm\MigrationsManager::unmigrateLast(true);
	}

	/**
	@Shortcut('unmigrate')
	@Usage('unmigrate filename')
	@Description('Automatically process migrations')
	*/
	public function unmigrateAction($request) {
		\Asgard\Core\App::get('clirouter')->run('Asgard\Core\Cli\DBController', 'backup', $request);
		echo 'Unmigrating...'."\n";

		\Asgard\Orm\MigrationsManager::unmigrate($request[0], true);
	}

	/**
	@Shortcut('migrate')
	@Usage('migrate filename')
	@Description('Automatically process migrations')
	*/
	public function migrateAction($request) {
		\Asgard\Core\App::get('clirouter')->run('Asgard\Core\Cli\DBController', 'backup', $request);
		echo 'Migrating...'."\n";

		\Asgard\Orm\MigrationsManager::migrate($request[0], true);
	}

	/**
	@Shortcut('migrate-all')
	@Usage('migrate-all')
	@Description('Automatically process migrations')
	*/
	public function migrateAllAction($request) {
		\Asgard\Core\App::get('clirouter')->run('Asgard\Core\Cli\DBController', 'backup', $request);
		echo 'Migrating...'."\n";

		\Asgard\Orm\MigrationsManager::migrateAll();
	}
}
