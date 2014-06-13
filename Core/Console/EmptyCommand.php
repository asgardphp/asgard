<?php
namespace Asgard\Core\Console;

class EmptyCommand extends \Asgard\Console\Command {
	protected $name = 'db:empty';
	protected $description = 'Empty the database';

	protected function execute() {
		$asgard = $this->getAsgard();
		$asgard['schema']->dropAll();
		$this->output->writeln('<info>The database was emptied with success.</info>');
	}
}