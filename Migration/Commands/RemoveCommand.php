<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Remove a migration command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class RemoveCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'migrations:remove';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Remove a migration';
	/**
	 * Migrations directory.
	 * @var string
	 */
	protected $migrationsDir;
	/**
	 * DB.
	 * @var \Asgard\Db\DBInterface
	 */
	protected $db;
	/**
	 * DB.
	 * @var \Asgard\Db\SchemaInterface
	 */
	protected $schema;

	/**
	 * Constructor.
	 * @param string $migrationsDir
	 */
	public function __construct($migrationsDir, \Asgard\Db\DBInterface $db, \Asgard\Db\SchemaInterface $schema) {
		$this->migrationsDir = $migrationsDir;
		$this->db = $db;
		$this->schema = $schema;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$migration = $this->input->getArgument('migration');

		$mm = new \Asgard\Migration\MigrationManager($this->migrationsDir, $this->db, $this->schema);
		$mm->remove($migration);
		if($mm->has($migration))
			$this->error('The migration could not be removed.');
		else
			$this->info('The migration has been successfully removed.');
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