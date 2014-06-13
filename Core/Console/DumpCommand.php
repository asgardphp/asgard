<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputArgument;

class DumpCommand extends \Asgard\Console\Command {
	protected $name = 'db:dump';
	protected $description = 'Dump the database';

	protected function execute() {
		$asgard = $this->getAsgard();
		$dst = $this->input->getArgument('dst') ? $this->input->getArgument('dst'):$asgard['kernel']['root'].'/storage/dumps/sql/'.time().'.sql';
		if($asgard['db']->dump($dst))
			$this->output->writeln('<info>The database was dumped with success.</info>');
		else
			$this->output->writeln('<error>The database could not be dumped.</error>');
	}

	protected function getArguments() {
		return [
			['dst', InputArgument::OPTIONAL, 'The destination'],
		];
	}
}