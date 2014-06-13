<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputArgument;

class MigrateOneCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:migrate';
	protected $description = 'Run a migration';

	protected function execute() {
		$migration = $this->input->getArgument('migration');
		$mm = new \Asgard\Migration\MigrationsManager($this->getAsgard()['kernel']['root'].'/migrations/', $this->getAsgard());

		if($mm->migrate($migration, true))
			$this->output->writeln('<info>Migration succeded.</info>');
		else
			$this->output->writeln('<error>Migration failed.</error>');
	}

	protected function getArguments() {
		return [
			['migration', InputArgument::REQUIRED, 'The migration'],
		];
	}
}