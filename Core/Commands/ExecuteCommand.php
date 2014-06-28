<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ExecuteCommand extends \Asgard\Console\Command {
	protected $name = 'execute';
	protected $description = 'Execute PHP code in the application context';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$cmd = $this->input->getArgument('cmd');
		try {
			if(preg_match('/^dump /', $cmd))
				$cmd = 'var_dump('.substr($cmd, 5).')';
			if(!preg_match('/;$/', $cmd))
				$cmd .= ';';
			eval($cmd);
		} catch(\Exception $e) {
			$this->error($e->getMessage());
		}
	}

	protected function getArguments() {
		return [
			['cmd', InputArgument::REQUIRED, 'PHP code'],
		];
	}
}