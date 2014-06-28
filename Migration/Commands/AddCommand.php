<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class AddCommand extends \Asgard\Console\Command {
	protected $name = 'migrations:add';
	protected $description = 'Add a new migration to the list';
	protected $migrationsDir;

	public function __construct($migrationsDir) {
		$this->migrationsDir = $migrationsDir;
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$src = $this->input->getArgument('src');

		$mm = new \Asgard\Migration\MigrationsManager($this->migrationsDir);
		$migration = $mm->add($src);
		if($mm->has($migration))
			$this->info('The migration was successfully added.');
		else
			$this->error('The migration could not be added.');
	}

	protected function getArguments() {
		return [
			['src', InputArgument::REQUIRED, 'The migration file'],
		];
	}
}