<?php
namespace Asgard\Db\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Empty the tables command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class EmptyCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'db:empty';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Empty the database';
	/**
	 * Database dependency.
	 * @var \Asgard\Db\DBInterface
	 */
	protected $db;

	/**
	 * Constructor.
	 * @param \Asgard\Db\DBInterface $db
	 */
	public function __construct(\Asgard\Db\DBInterface $db) {
		$this->db = $db;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$schema = new \Asgard\DB\Schema($this->db);
		$schema->dropAll();
		$this->info('The database was emptied with success.');
	}
}