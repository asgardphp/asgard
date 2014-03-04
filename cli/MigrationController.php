<?php
namespace Asgard\ORM\CLI;

class MigrationController extends \Asgard\Cli\CLIController {
	/**
	@Shortcut('automigrate')
	@Usage('automigrate')
	@Description('Automacally build and process a migration from the Entities')
	*/
	public function automigrateAction($request) {
		$this->diffAction($request);
		$this->migrateAction($request);
	}

	/**
	@Shortcut('diff')
	@Usage('diff')
	@Description('Automatically build a migration from the Entities')
	*/
	public function diffAction($request) {
		if(!ORMManager::uptodate())
			die('You must run all migrations before using diff.');
			
		FileManager::mkdir('migrations');
		echo 'Running diff..'."\n";
	
		echo 'New migration: '.ORMManager::diff(true);
	}

	/**
	@Shortcut('migrate')
	@Usage('migrate')
	@Description('Automatically process migrations')
	*/
	public function migrateAction($request) {
		CLIRouter::run('Asgard\Cli\DB', 'backup', $request);
		echo 'Migrating...'."\n";

		ORMManager::migrate(true);
	}
}
