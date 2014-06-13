<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputArgument;

class AutoMigrateCommand extends \Asgard\Console\Command {
	protected $name = 'orm:automigrate';
	protected $description = 'Generate and run a migration from ORM entities';

	protected function execute() {
		$asgard = $this->getAsgard();
		$migration = $this->input->getArgument('migration') ? $this->input->getArgument('migration'):'Automigrate';

		$mm = new \Asgard\Migration\MigrationsManager($this->getAsgard()['kernel']['root'].'/migrations/', $asgard);
		$om = new \Asgard\Orm\ORMMigrations($mm);
		
		$entities = [];

		$bundles = $asgard['kernel']->getAllBundles();
		foreach($bundles as $bundle) {
			$bundle = $bundle->getPath();
			foreach(glob($bundle.'/Entities/*.php') as $file) {
				$class = \Asgard\Common\Tools::loadClassFile($file);
				if(is_subclass_of($class, 'Asgard\Entity\Entity'))
					$entities[] = $class;
			}
		}

		$migration = $om->generateMigration($entities, $migration, $this->getAsgard()['db']);
		if($mm->has($migration))
			$this->output->writeln('<info>The migration was successfully generated.</info>');
		else
			$this->output->writeln('<error>The migration could not be generated.</error>');

		if($mm->migrate($migration, true))
			$this->output->writeln('<info>Migration succeded.</info>');
		else
			$this->output->writeln('<error>Migration failed.</error>');
	}

	protected function getArguments() {
		return [
			['migration', InputArgument::OPTIONAL, 'The migration name'],
		];
	}
}