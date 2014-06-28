<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateCommand extends \Asgard\Console\Command {
	protected $name = 'migrate';
	protected $description = 'Run the migrations';
	protected $migrationsDir;

	public function __construct($migrationsDir) {
		$this->migrationsDir = $migrationsDir;
		parent::__construct();
	}


	protected function execute(InputInterface $input, OutputInterface $output) {
		$mm = new \Asgard\Migration\MigrationsManager($this->migrationsDir, $this->getContainer());

		if(!$mm->getTracker()->getDownList())
			$this->output->writeln('Nothing to migrate.');
		elseif($mm->migrateAll(true))
			$this->info('Migration succeded.');
		else
			$this->error('Migration failed.');
	}
}