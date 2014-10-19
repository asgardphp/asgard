<?php
namespace Asgard\Db\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Dump the database command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class DumpCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'db:dump';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Dump the database';
	/**
	 * Detination directory.
	 * @var string
	 */
	protected $dir;
	/**
	 * Database dependency.
	 * @var \Asgard\Db\DBInterface
	 */
	protected $db;

	/**
	 * Constructor.
	 * @param \Asgard\Db\DBInterface $db
	 * @param string                 $dir
	 */
	public function __construct(\Asgard\Db\DBInterface $db, $dir) {
		$this->db = $db;
		$this->dir = $dir;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$dst = $this->input->getArgument('dst') ? $this->input->getArgument('dst'):$this->dir.'/'.time().'.sql';
		if($this->db->dump($dst))
			$this->info('The database was dumped with success.');
		else
			$this->error('The database could not be dumped.');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['dst', InputArgument::OPTIONAL, 'The destination'],
		];
	}
}