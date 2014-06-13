<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputArgument;

class UnmigrateCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:unmigrate';
	protected $description = 'Unmigrate a migration';

	protected function execute() {
		$migration = $this->input->getArgument('migration');
		$mm = new \Asgard\Migration\MigrationsManager($this->getAsgard()['kernel']['root'].'/migrations/', $this->getAsgard());

		if($mm->unmigrate($migration, true))
			$this->output->writeln('<info>Unmigration succeded.</info>');
		else
			$this->output->writeln('<error>Unmigration failed.</error>');
	}

	protected function getArguments() {
		return [
			['migration', InputArgument::REQUIRED, 'The migration'],
		];
	}
}