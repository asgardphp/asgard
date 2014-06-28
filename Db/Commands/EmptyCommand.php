<?php
namespace Asgard\Db\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EmptyCommand extends \Asgard\Console\Command {
	protected $name = 'db:empty';
	protected $description = 'Empty the database';
	protected $db;

	public function __construct($db) {
		$this->db = $db;
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$schema = new \Asgard\DB\Schema($this->db);
		$schema->dropAll();
		$this->info('The database was emptied with success.');
	}
}