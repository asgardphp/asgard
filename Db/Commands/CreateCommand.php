<?php
namespace Asgard\Db\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Create the database command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class CreateCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'db:create';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Create the database';
	/**
	 * Configuration directory.
	 * @var \Asgard\Db\DB
	 */
	protected $db;

	/**
	 * Constructor.
	 * @param \Asgard\Db\DB $db
	 */
	public function __construct(\Asgard\Db\DB $db) {
		$this->db = $db;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$database = $this->db->getConfig()['database'];
		$driver = $this->db->getConfig()['driver'];

		if($driver == 'sqlite') {
			if(file_exists($database))
				$this->comment('The database "'.$database.'" already exists.');
			else {
				\Asgard\File\FileSystem::write($database, '');
				$this->info('Database created.');
			}
		}
		else {
			$config = $this->db->getConfig();
			unset($config['database']);
			$this->db->buildPDO($config);
			$this->db->getSchema()->getSchemaManager()->createDatabase($database);
			$this->info('Database created.');
		}
	}
}