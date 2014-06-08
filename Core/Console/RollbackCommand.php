<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RollbackCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:rollback';
	protected $description = 'Rollback the last database migration';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$mm = new \Asgard\Migration\MigrationsManager($this->getAsgard()['kernel']->getRoot().'/migrations/');
		if($migration = $mm->rollback())
			$output->writeln('<info>Rollback successful.</info>');
		else
			$output->writeln('<error>Rollback unsuccessful.</error>');
	}
}