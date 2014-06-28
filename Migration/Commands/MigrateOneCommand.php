<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class MigrateOneCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:migrate';
	protected $description = 'Run a migration';
	protected $migrationsDir;

	public function __construct($migrationsDir) {
		$this->migrationsDir = $migrationsDir;
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$migration = $this->input->getArgument('migration');
		$mm = new \Asgard\Migration\MigrationsManager($this->migrationsDir, $this->getContainer());

		if($mm->migrate($migration, true))
			$this->info('Migration succeded.');
		else
			$this->error('Migration failed.');
	}

	protected function getArguments() {
		return [
			['migration', InputArgument::REQUIRED, 'The migration'],
		];
	}
}