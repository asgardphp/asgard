<?php
namespace Asgard\Db\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class RestoreCommand extends \Asgard\Console\Command {
	protected $name = 'db:restore';
	protected $description = 'Restore the database';
	protected $db;

	public function __construct($db) {
		$this->db = $db;
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$src = $this->input->getArgument('src');
		$schema = new \Asgard\Db\Schema($this->db);
		$schema->dropAll();
		if($this->db->import($src))
			$this->info('The database was restored with success.');
		else
			$this->error('The database could not be restored.');
	}

	protected function getArguments() {
		return [
			['src', InputArgument::REQUIRED, 'The source'],
		];
	}
}