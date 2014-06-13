<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputArgument;

class GenerateMigrationCommand extends \Asgard\Console\Command {
	protected $name = 'orm:generate';
	protected $description = 'Generate a migration from ORM entities';

	protected function execute() {
		$asgard = $this->getAsgard();
		$migration = $this->input->getArgument('migration') ? $this->input->getArgument('migration'):'Automigrate';

		$mm = new \Asgard\Migration\MigrationsManager($this->getAsgard()['kernel']['root'].'/migrations/', $asgard);
		$om = new \Asgard\Orm\ORMMigrations($mm);
		
		$entities = [];

		$bundles = $asgard['bundlesManager']->getBundlesPath();
		foreach($bundles as $bundle) {
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
	}

	protected function getArguments() {
		return [
			['migration', InputArgument::OPTIONAL, 'The migration name'],
		];
	}
}