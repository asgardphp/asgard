<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RefreshCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:refresh';
	protected $description = 'Reset and re-run all migrations';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$mm = new \Asgard\Migration\MigrationsManager($this->getAsgard()['kernel']->getRoot().'/migrations/', $this->getAsgard());

		$mm->reset();

		if(!$mm->getTracker()->getDownList())
			$output->writeln('Nothing to migrate.');
		elseif($mm->migrateAll(true))
			$output->writeln('<info>Refresh succeded.</info>');
		else
			$output->writeln('<error>Refresh failed.</error>');
	}
}