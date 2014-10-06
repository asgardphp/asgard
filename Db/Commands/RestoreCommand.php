<?php
namespace Asgard\Db\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Restore a backup command.
 */
class RestoreCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'db:restore';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Restore the database';
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
		$src = $this->input->getArgument('src');
		$schema = new \Asgard\Db\Schema($this->db);
		$schema->dropAll();
		if($this->db->import($src))
			$this->info('The database was restored with success.');
		else
			$this->error('The database could not be restored.');
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['src', InputArgument::REQUIRED, 'The source'],
		];
	}
}