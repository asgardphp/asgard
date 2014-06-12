<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UnmigrateCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:unmigrate';
	protected $description = 'Unmigrate a migration';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$migration = $input->getArgument('migration');
		$mm = new \Asgard\Migration\MigrationsManager($this->getAsgard()['kernel']->getRoot().'/migrations/', $this->getAsgard());

		if($mm->unmigrate($migration, true))
			$output->writeln('<info>Unmigration succeded.</info>');
		else
			$output->writeln('<error>Unmigration failed.</error>');
	}

	protected function getArguments() {
		return [
			['migration', InputArgument::REQUIRED, 'The migration'],
		];
	}
}