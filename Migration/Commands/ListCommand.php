<?php
namespace Asgard\Migration\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * List migrations command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ListCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'migrations:list';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Displays the list of migrations';
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
	 * Constructor.
	 * @param string $migrationsDir
	 */
	public function __construct($migrationsDir, \Asgard\Db\DBInterface $db) {
		$this->migrationsDir = $migrationsDir;
		$this->db = $db;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$table = new \Symfony\Component\Console\Helper\Table($this->output);
		$table->setHeaders(['Name', 'Status', 'Migrated', 'Added']);

		$tracker = new \Asgard\Migration\Tracker($this->migrationsDir, $this->db);
		foreach($tracker->getList() as $migration=>$params)
			$table->addRow([$migration, isset($params['migrated']) ? 'up':'down', isset($params['migrated']) ? date('d/m/Y H:i:s', $params['migrated']):'', date('d/m/Y H:i:s', $params['added'])]);

		$table->render($this->output);
	}
}