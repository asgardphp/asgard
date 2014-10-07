<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Unmigrate command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class UnmigrateCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'migrations:unmigrate';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Unmigrate a migration';
	/**
	 * Migrations directory.
	 * @var string
	 */
	protected $migrationsDir;

	/**
	 * Constructor.
	 * @param string $migrationsDir
	 */
	public function __construct($migrationsDir) {
		$this->migrationsDir = $migrationsDir;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$migration = $this->input->getArgument('migration');
		$mm = new \Asgard\Migration\MigrationsManager($this->migrationsDir, $this->getContainer());

		if($mm->unmigrate($migration))
			$this->info('Unmigration succeded.');
		else
			$this->error('Unmigration failed.');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['migration', InputArgument::REQUIRED, 'The migration name'],
		];
	}
}