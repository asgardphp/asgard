<?php
namespace Asgard\Core\Console;

class CacheCommand extends \Asgard\Console\Command {
	protected $name = 'cc';
	protected $description = 'Flush the application cache';

	protected function execute() {
		if($this->getAsgard()['cache']->clear())
			$this->output->writeln('<info>The cache has been cleared.</info>');
		else
			$this->output->writeln('<error>The cache could not be cleared.</error>');
	}
}