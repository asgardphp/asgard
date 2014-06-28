<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:refresh';
	protected $description = 'Reset and re-run all migrations';
	protected $migrationsDir;

	public function __construct($migrationsDir) {
		$this->migrationsDir = $migrationsDir;
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$mm = new \Asgard\Migration\MigrationsManager($this->migrationsDir, $this->getContainer());

		$mm->reset();

		if(!$mm->getTracker()->getDownList())
			$this->output->writeln('Nothing to migrate.');
		elseif($mm->migrateAll(true))
			$this->info('Refresh succeded.');
		else
			$this->error('Refresh failed.');
	}
}