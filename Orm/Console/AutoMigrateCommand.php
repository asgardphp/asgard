<?php
namespace Asgard\Migration\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AutoMigrateCommand extends \Asgard\Console\Command {
	protected $name = 'orm:automigrate';
	protected $description = 'Generate and run a migration from ORM entities';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$asgard = $this->getAsgard();
		$migration = $input->getArgument('migration') ? $input->getArgument('migration'):'Automigrate';

		$mm = new \Asgard\Migration\MigrationsManager($this->getAsgard()['kernel']['root'].'/migrations/', $asgard);
		if($mm->getTracker()->getDownList()) {
			$output->writeln('<error>All migrations must have been executed before running automigrate.</error>');
			return;
		}
		$om = new \Asgard\Orm\ORMMigrations($mm);
		
		$entities = array();

		$bundles = $asgard['kernel']->getAllBundles();
		foreach($bundles as $bundle) {
			$bundle = $bundle->getPath();
			foreach(glob($bundle.'/Entities/*.php') as $file) {
				$class = $asgard['autoloader']->loadClassFile($file);
				if(is_subclass_of($class, 'Asgard\Entity\Entity'))
					$entities[] = $class;
			}
		}

		$migration = $om->generateMigration($entities, $migration, $this->getAsgard()['db']);
		if($mm->has($migration))
			$output->writeln('<info>The migration was successfully generated.</info>');
		else
			$output->writeln('<error>The migration could not be generated.</error>');

		if($mm->migrateAll(true))
			$output->writeln('<info>Migration succeded.</info>');
		else
			$output->writeln('<error>Migration failed.</error>');
	}

	protected function getArguments() {
		return array(
			array('migration', InputArgument::OPTIONAL, 'The migration name'),
		);
	}
}