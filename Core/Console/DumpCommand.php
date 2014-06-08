<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DumpCommand extends \Asgard\Console\Command {
	protected $name = 'db:dump';
	protected $description = 'Dump the database';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$asgard = $this->getAsgard();
		$dst = $input->getArgument('dst') ? $input->getArgument('dst'):$asgard['kernel']['root'].'/storage/dumps/sql/'.time().'.sql';
		if($asgard['db']->dump($dst))
			$output->writeln('<info>The database was dumped with success.</info>');
		else
			$output->writeln('<error>The database could not be dumped.</error>');
	}

	protected function getArguments() {
		return array(
			array('dst', InputArgument::OPTIONAL, 'The destination'),
		);
	}
}