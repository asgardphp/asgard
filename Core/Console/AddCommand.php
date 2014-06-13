<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputArgument;

class AddCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:add';
	protected $description = 'Add a new migration to the list';

	protected function execute() {
		$src = $this->input->getArgument('src');

		$mm = new \Asgard\Migration\MigrationsManager($this->getAsgard()['kernel']['root'].'/migrations/');
		$migration = $mm->add($src);
		if($mm->has($migration))
			$this->output->writeln('<info>The migration was successfully added.</info>');
		else
			$this->output->writeln('<error>The migration could not be added.</error>');
	}

	protected function getArguments() {
		return [
			['src', InputArgument::REQUIRED, 'The migration file'],
		];
	}
}