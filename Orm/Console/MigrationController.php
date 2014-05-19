<?php
namespace Asgard\Orm\Console;

class MigrationController extends \Asgard\Console\Controller {
	/**
	@Shortcut('automigrate')
	@Usage('automigrate')
	@Description('Automacally build and process a migration from the Entities')
	*/
	public function automigrateAction($request) {
		\Asgard\Utils\FileManager::mkdir('migrations');
		echo 'Running diff..'."\n";
		$filename = $this->getMigrationsManager()->diff('Diff', true);
		if(!$filename) {
			echo 'Nothing to migrate.';
			return;
		}
		echo 'New migration: '.$filename;

		\Asgard\Core\App::get('clirouter')->run('Asgard\Core\Cli\DBController', 'backup', $request);
		echo 'Migrating...'."\n";
		$this->getMigrationsManager()->migrate($filename, true);
	}

	/**
	@Shortcut('diff')
	@Usage('diff')
	@Description('Automatically build a migration from the Entities')
	*/
	public function diffAction($request) {
		\Asgard\Utils\FileManager::mkdir('migrations');
		echo 'Running diff..'."\n";
	
		$diff = $this->getMigrationsManager()->diff('Diff', true);
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

		$this->getMigrationsManager()->migrateNext(true);
	}

	/**
	@Shortcut('unmigrate-last')
	@Usage('unmigrate-last')
	@Description('Automatically process migrations')
	*/
	public function unmigrateLastAction($request) {
		\Asgard\Core\App::get('clirouter')->run('Asgard\Core\Cli\DBController', 'backup', $request);
		echo 'Unmigrating...'."\n";

		$this->getMigrationsManager()->unmigrateLast(true);
	}

	/**
	@Shortcut('unmigrate')
	@Usage('unmigrate filename')
	@Description('Automatically process migrations')
	*/
	public function unmigrateAction($request) {
		\Asgard\Core\App::get('clirouter')->run('Asgard\Core\Cli\DBController', 'backup', $request);
		echo 'Unmigrating...'."\n";

		$this->getMigrationsManager()->unmigrate($request[0], true);
	}

	/**
	@Shortcut('migrate')
	@Usage('migrate filename')
	@Description('Automatically process migrations')
	*/
	public function migrateAction($request) {
		\Asgard\Core\App::get('clirouter')->run('Asgard\Core\Cli\DBController', 'backup', $request);
		echo 'Migrating...'."\n";

		$this->getMigrationsManager()->migrate($request[0], true);
	}

	/**
	@Shortcut('migrate-all')
	@Usage('migrate-all')
	@Description('Automatically process migrations')
	*/
	public function migrateAllAction($request) {
		\Asgard\Core\App::get('clirouter')->run('Asgard\Core\Cli\DBController', 'backup', $request);
		echo 'Migrating...'."\n";

		$this->getMigrationsManager()->migrateAll();
	}

	protected function getMigrationsManager() {
		return \Asgard\Core\App::instance()->get('migrationsManager');
	}
}
