<?php
namespace Asgard\Migration\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MigrateCommand extends \Asgard\Console\Command {
	protected $name = 'migrate';
	protected $description = 'Run the migrations';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$mm = new \Asgard\Migration\MigrationsManager($this->getAsgard()['kernel']->getRoot().'/migrations/', $this->getAsgard());

		if(!$mm->getTracker()->getDownList())
			$output->writeln('Nothing to migrate.');
		elseif($mm->migrateAll(true))
			$output->writeln('<info>Migration succeded.</info>');
		else
			$output->writeln('<error>Migration failed.</error>');
	}
}