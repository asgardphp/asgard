<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RestoreCommand extends \Asgard\Console\Command {
	protected $name = 'db:restore';
	protected $description = 'Restore the database';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$asgard = $this->getAsgard();
		$src = $input->getArgument('src');
		$asgard['schema']->dropAll();
		if($asgard['db']->import($src))
			$output->writeln('<info>The database was restored with success.</info>');
		else
			$output->writeln('<error>The database could not be restored.</error>');
	}

	protected function getArguments() {
		return [
			['src', InputArgument::REQUIRED, 'The source'],
		];
	}
}