<?php
namespace Asgard\Core\Console;

class RefreshCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:refresh';
	protected $description = 'Reset and re-run all migrations';

	protected function execute() {
		$mm = new \Asgard\Migration\MigrationsManager($this->getAsgard()['kernel']['root'].'/migrations/', $this->getAsgard());

		$mm->reset();

		if(!$mm->getTracker()->getDownList())
			$this->output->writeln('Nothing to migrate.');
		elseif($mm->migrateAll(true))
			$this->output->writeln('<info>Refresh succeded.</info>');
		else
			$this->output->writeln('<error>Refresh failed.</error>');
	}
}