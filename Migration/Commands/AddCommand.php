<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Add a migration command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class AddCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'migrations:add';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Add a new migration to the list';
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
	 * @param string                     $migrationsDir
	 * @param \Asgard\Db\DBInterface     $db
	 * @param \Asgard\Db\SchemaInterface $schema
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
		$src = $this->input->getArgument('src');

		$mm = new \Asgard\Migration\MigrationManager($this->migrationsDir, $this->db, $this->schema);
		$migration = $mm->add($src);
		if($mm->has($migration))
			$this->info('The migration was successfully added.');
		else
			$this->error('The migration could not be added.');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['src', InputArgument::REQUIRED, 'The migration file'],
		];
	}
}