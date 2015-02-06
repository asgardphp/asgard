<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Migrate a migration command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class MigrateOneCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'migrations:migrate';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Run a migration';
	/**
	 * Migrations directory.
	 * @var string
	 */
	protected $migrationsDir;
	/**
	 * DB dependency.
	 * @var \Asgard\Db\DBInterface
	 */
	protected $db;
	/**
	 * Schema dependency.
	 * @var \Asgard\Db\SchemaInterface
	 */
	protected $schema;

	/**
	 * Constructor.
	 * @param string                     $migrationsDir
	 * @param \Asgard\Db\DBInterface     $db
	 * @param \Asgard\Db\SchemaInterface $schema
	 */
	public function __construct($migrationsDir, \Asgard\Db\DBInterface $db=null, \Asgard\Db\SchemaInterface $schema=null) {
		$this->migrationsDir = $migrationsDir;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$migration = $this->input->getArgument('migration');
		$mm = new \Asgard\Migration\MigrationManager($this->migrationsDir, $this->getContainer());
		if($this->db)
			$mm->setDB($this->db);
		if($this->schema)
			$mm->setSchema($this->schema);

		if($mm->migrate($migration, true))
			$this->info('Migration succeded.');
		else
			$this->error('Migration failed.');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['migration', InputArgument::REQUIRED, 'The migration'],
		];
	}
}