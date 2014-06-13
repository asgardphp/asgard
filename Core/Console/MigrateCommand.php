<?php
namespace Asgard\Core\Console;

class MigrateCommand extends \Asgard\Console\Command {
	protected $name = 'migrate';
	protected $description = 'Run the migrations';

	protected function execute() {
		$mm = new \Asgard\Migration\MigrationsManager($this->getAsgard()['kernel']['root'].'/migrations/', $this->getAsgard());

		if(!$mm->getTracker()->getDownList())
			$this->output->writeln('Nothing to migrate.');
		elseif($mm->migrateAll(true))
			$this->output->writeln('<info>Migration succeded.</info>');
		else
			$this->output->writeln('<error>Migration failed.</error>');
	}
}