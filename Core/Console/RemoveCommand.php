<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RemoveCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:remove';
	protected $description = 'Remove a migration';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$migration = $input->getArgument('migration');

		$mm = new \Asgard\Migration\MigrationsManager($this->getAsgard()['kernel']['root'].'/migrations/');
		$mm->remove($migration);
		if($mm->has($migration))
			$output->writeln('<error>The migration could not be removed.</error>');
		else
			$output->writeln('<info>The migration has been successfully removed.</info>');
	}

	protected function getArguments() {
		return [
			['migration', InputArgument::REQUIRED, 'The migration name'],
		];
	}
}