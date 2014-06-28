<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RollbackCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:rollback';
	protected $description = 'Rollback the last database migration';
	protected $migrationsDir;

	public function __construct($migrationsDir) {
		$this->migrationsDir = $migrationsDir;
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$mm = new \Asgard\Migration\MigrationsManager($this->migrationsDir);
		if($mm->rollback())
			$this->info('Rollback successful.');
		else
			$this->error('Rollback unsuccessful.');
	}
}