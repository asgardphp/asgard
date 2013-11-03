<?php
#todo delete old model tables
namespace Coxis\ORM\CLI;

class MigrationController extends \Coxis\Cli\CLIController {
	/**
	@Shortcut('automigrate')
	@Usage('automigrate')
	@Description('Automacally build and process a migration from the models')
	*/
	public function automigrateAction($request) {
		$this->diffAction($request);
		$this->migrateAction($request);
	}

	/**
	@Shortcut('diff')
	@Usage('diff')
	@Description('Automatically build a migration from the models')
	*/
	public function diffAction($request) {
		#todo check migration version
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
		CLIRouter::run('Coxis\Core\Cli\DB', 'backup', $request);
		echo 'Migrating...'."\n";

		ORMManager::migrate(true);
	}
}
