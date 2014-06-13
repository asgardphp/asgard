<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputArgument;

class RestoreCommand extends \Asgard\Console\Command {
	protected $name = 'db:restore';
	protected $description = 'Restore the database';

	protected function execute() {
		$asgard = $this->getAsgard();
		$src = $this->input->getArgument('src');
		$asgard['schema']->dropAll();
		if($asgard['db']->import($src))
			$this->output->writeln('<info>The database was restored with success.</info>');
		else
			$this->output->writeln('<error>The database could not be restored.</error>');
	}

	protected function getArguments() {
		return [
			['src', InputArgument::REQUIRED, 'The source'],
		];
	}
}