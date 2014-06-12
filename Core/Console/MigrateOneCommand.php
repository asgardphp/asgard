<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MigrateOneCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:migrate';
	protected $description = 'Run a migration';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$migration = $input->getArgument('migration');
		$mm = new \Asgard\Migration\MigrationsManager($this->getAsgard()['kernel']->getRoot().'/migrations/', $this->getAsgard());

		if($mm->migrate($migration, true))
			$output->writeln('<info>Migration succeded.</info>');
		else
			$output->writeln('<error>Migration failed.</error>');
	}

	protected function getArguments() {
		return [
			['migration', InputArgument::REQUIRED, 'The migration'],
		];
	}
}