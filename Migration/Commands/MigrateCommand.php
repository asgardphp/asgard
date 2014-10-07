<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Migrate command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class MigrateCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'migrate';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Run the migrations';
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

		if(!$mm->getTracker()->getDownList())
			$this->output->writeln('Nothing to migrate.');
		elseif($mm->migrateAll(true))
			$this->info('Migration succeded.');
		else
			$this->error('Migration failed.');
	}
}