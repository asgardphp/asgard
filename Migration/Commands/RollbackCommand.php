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
	 * DB dependency.
	 * @var string
	 */
	protected $db;
	/**
	 * Schema dependency.
	 * @var string
	 */
	protected $schema;

	/**
	 * Constructor.
	 * @param string                     $migrationsDir
	 * @param \Asgard\Db\DbInterface     $db
	 * @param \Asgard\Db\SchemaInterface $schema
	 */
	public function __construct($migrationsDir, \Asgard\Db\DBInterface $db=null, \Asgard\Db\SchemaInterface $schema=null) {
		$this->migrationsDir = $migrationsDir;
		$this->db = $db;
		$this->schema = $schema;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$mm = new \Asgard\Migration\MigrationManager($this->migrationsDir, $this->getContainer());
		if($this->db)
			$mm->setDB($this->db);
		if($this->schema)
			$mm->setSchema($this->schema);

		if($mm->rollback())
			$this->info('Rollback successful.');
		else
			$this->error('Rollback unsuccessful.');
	}
}