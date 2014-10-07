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
		$table = $this->getHelperSet()->get('table');
		$table->setHeaders(['Name', 'Status', 'Migrated', 'Added']);

		$tracker = new \Asgard\Migration\Tracker($this->migrationsDir);
		foreach($tracker->getList() as $migration=>$params)
			$table->addRow([$migration, isset($params['migrated']) ? 'up':'down', isset($params['migrated']) ? date('d/m/Y H:i:s', $params['migrated']):'', date('d/m/Y H:i:s', $params['added'])]);

		$table->render($this->output);
	}
}