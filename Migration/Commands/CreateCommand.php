<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Create a migration command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class CreateCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'migrations:create';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Create a new migration to the list';
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
		$migration = $this->input->getArgument('migration') ? $this->input->getArgument('migration'):'Migration';

		$mm = new \Asgard\Migration\MigrationManager($this->migrationsDir);

		$name = $mm->create('', '', $migration, '\Asgard\Migration\DBMigration');
		if($mm->has($name))
			$this->info('The migration was successfully creted.');
		else
			$this->error('The migration could not be created.');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['migration', InputArgument::OPTIONAL, 'The migration name'],
		];
	}
}