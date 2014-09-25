<?php
namespace Asgard\Orm\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class AutoMigrateCommand extends \Asgard\Console\Command {
	protected $name = 'orm:automigrate';
	protected $description = 'Generate and run a migration from ORM entities';
	protected $entitiesManager;
	protected $migrationsManager;
	protected $dataMapper;

	public function __construct($entitiesManager, $migrationsManager, $dataMapper) {
		$this->entitiesManager = $entitiesManager;
		$this->migrationsManager = $migrationsManager;
		$this->dataMapper = $dataMapper;
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$migration = $this->input->getArgument('migration') ? $this->input->getArgument('migration'):'Automigrate';

		$dm = $this->dataMapper;
		$mm = $this->migrationsManager;
		$om = new \Asgard\Orm\ORMMigrations($dm, $mm);
		
		$definitions = [];
		foreach($this->entitiesManager->getDefinitions() as $definition) {
			if($definition->get('ormMigrate'))
				$definitions[] = $definition;
		}
		$migration = $om->generateMigration($definitions, $migration);
		if($mm->has($migration))
			$this->info('The migration was successfully generated.');
		else
			$this->error('The migration could not be generated.');

		if($mm->migrate($migration, true))
			$this->info('Migration succeded.');
		else
			$this->error('Migration failed.');
	}

	protected function getArguments() {
		return [
			['migration', InputArgument::OPTIONAL, 'The migration name'],
		];
	}
}