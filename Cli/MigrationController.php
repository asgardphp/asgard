<?php
namespace Asgard\Orm\CLI;

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
		if(!\Asgard\Orm\Libs\ORMManager::uptodate())
			die('You must run all migrations before using diff.');
			
		\Asgard\Utils\FileManager::mkdir('migrations');
		echo 'Running diff..'."\n";
	
		echo 'New migration: '.\Asgard\Orm\Libs\ORMManager::diff(true);
	}

	/**
	@Shortcut('migrate')
	@Usage('migrate')
	@Description('Automatically process migrations')
	*/
	public function migrateAction($request) {
		\Asgard\Core\App::get('clirouter')->run('Asgard\Cli\DBController', 'backup', $request);
		echo 'Migrating...'."\n";

		\Asgard\Orm\Libs\ORMManager::migrate(true);
	}
}
