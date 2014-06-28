<?php
namespace Asgard\Orm\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class GenerateMigrationCommand extends \Asgard\Console\Command {
	protected $name = 'orm:generate';
	protected $description = 'Generate a migration from ORM entities';
	protected $entitiesManager;
	protected $migrationsManager;
	protected $db;

	public function __construct($entitiesManager, $migrationsManager, $db) {
		$this->entitiesManager = $entitiesManager;
		$this->migrationsManager = $migrationsManager;
		$this->db = $db;
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$migration = $this->input->getArgument('migration') ? $this->input->getArgument('migration'):'Automigrate';

		$mm = $this->migrationsManager;
		$om = new \Asgard\Orm\ORMMigrations($mm);

		$entities = $this->entitiesManager->getEntities();

		$migration = $om->generateMigration($entities, $migration, $this->db);
		if($mm->has($migration))
			$this->info('The migration was successfully generated.');
		else
			$this->error('The migration could not be generated.');
	}

	protected function getArguments() {
		return [
			['migration', InputArgument::OPTIONAL, 'The migration name'],
		];
	}
}