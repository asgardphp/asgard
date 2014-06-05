<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CacheCommand extends \Asgard\Console\Command {
	protected $name = 'cc';
	protected $description = 'Flush the application cache';

	protected function execute(InputInterface $input, OutputInterface $output) {
		if($this->getAsgard()['cache']->clear())
			$output->writeln('<info>The cache has been cleared.</info>');
		else
			$output->writeln('<error>The cache could not be cleared.</error>');
	}
}