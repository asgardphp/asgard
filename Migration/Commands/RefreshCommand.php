<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Refresh migrations command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class RefreshCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'migrations:refresh';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Reset and re-run all migrations';
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