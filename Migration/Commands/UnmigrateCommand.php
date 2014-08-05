<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class UnmigrateCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:unmigrate';
	protected $description = 'Unmigrate a migration';
	protected $migrationsDir;

	public function __construct($migrationsDir) {
		$this->migrationsDir = $migrationsDir;
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$migration = $this->input->getArgument('migration');
		$mm = new \Asgard\Migration\MigrationsManager($this->migrationsDir, $this->getContainer());

		if($mm->unmigrate($migration))
			$this->info('Unmigration succeded.');
		else
			$this->error('Unmigration failed.');
	}

	protected function getArguments() {
		return [
			['migration', InputArgument::REQUIRED, 'The migration name'],
		];
	}
}