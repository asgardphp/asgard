<?php
namespace Asgard\Core\Console;

class RollbackCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:rollback';
	protected $description = 'Rollback the last database migration';

	protected function execute() {
		$mm = new \Asgard\Migration\MigrationsManager($this->getAsgard()['kernel']['root'].'/migrations/');
		if($mm->rollback())
			$this->output->writeln('<info>Rollback successful.</info>');
		else
			$this->output->writeln('<error>Rollback unsuccessful.</error>');
	}
}