<?php
namespace Asgard\Cache\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearCommand extends \Asgard\Console\Command {
	protected $name = 'cc';
	protected $description = 'Clear the application cache';
	protected $cache;

	public function __construct($cache) {
		$this->cache = $cache;
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		if($this->cache->clear())
			$this->info('The cache has been cleared.');
		else
			$this->error('The cache could not be cleared.');
	}
}