<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Rollback command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class RollbackCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'migrations:rollback';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Rollback the last database migration';
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
		$mm = new \Asgard\Migration\MigrationManager($this->migrationsDir);
		if($mm->rollback())
			$this->info('Rollback successful.');
		else
			$this->error('Rollback unsuccessful.');
	}
}