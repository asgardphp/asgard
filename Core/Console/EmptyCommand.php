<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class EmptyCommand extends \Asgard\Console\Command {
	protected $name = 'db:empty';
	protected $description = 'Empty the database';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$asgard = $this->getAsgard();
		$asgard['schema']->dropAll();
		$output->writeln('<info>The database was emptied with success.</info>');
	}
}