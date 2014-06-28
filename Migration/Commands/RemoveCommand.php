<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class RemoveCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:remove';
	protected $description = 'Remove a migration';
	protected $migrationsDir;

	public function __construct($migrationsDir) {
		$this->migrationsDir = $migrationsDir;
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$migration = $this->input->getArgument('migration');

		$mm = new \Asgard\Migration\MigrationsManager($this->migrationsDir);
		$mm->remove($migration);
		if($mm->has($migration))
			$this->error('The migration could not be removed.');
		else
			$this->info('The migration has been successfully removed.');
	}

	protected function getArguments() {
		return [
			['migration', InputArgument::REQUIRED, 'The migration name'],
		];
	}
}