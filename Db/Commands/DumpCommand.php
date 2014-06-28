<?php
namespace Asgard\Db\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class DumpCommand extends \Asgard\Console\Command {
	protected $name = 'db:dump';
	protected $description = 'Dump the database';
	protected $dir;
	protected $db;

	public function __construct($db, $dir) {
		$this->db = $db;
		$this->dir = $dir;
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$dst = $this->input->getArgument('dst') ? $this->input->getArgument('dst'):$this->dir.'/'.time().'.sql';
		if($this->db->dump($dst))
			$this->info('The database was dumped with success.');
		else
			$this->error('The database could not be dumped.');
	}

	protected function getArguments() {
		return [
			['dst', InputArgument::OPTIONAL, 'The destination'],
		];
	}
}