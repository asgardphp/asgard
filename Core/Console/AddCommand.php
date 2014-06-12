<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AddCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:add';
	protected $description = 'Add a new migration to the list';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$src = $input->getArgument('src');

		$mm = new \Asgard\Migration\MigrationsManager($this->getAsgard()['kernel']['root'].'/migrations/');
		$migration = $mm->add($src);
		if($mm->has($migration))
			$output->writeln('<info>The migration was successfully added.</info>');
		else
			$output->writeln('<error>The migration could not be added.</error>');
	}

	protected function getArguments() {
		return [
			['src', InputArgument::REQUIRED, 'The migration file'],
		];
	}
}