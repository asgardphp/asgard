<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputArgument;

class ExecuteCommand extends \Asgard\Console\Command {
	protected $name = 'execute';
	protected $description = 'Execute PHP code in the application context';

	protected function execute() {
		$cmd = $this->input->getArgument('cmd');
		try {
			if(preg_match('/^dump /', $cmd))
				$cmd = 'var_dump('.substr($cmd, 5).')';
			if(!preg_match('/;$/', $cmd))
				$cmd .= ';';
			eval($cmd);
		} catch(\Exception $e) {
			$this->output->writeln('<error>'.$e->getMessage().'</error>');
		}
	}

	protected function getArguments() {
		return [
			['cmd', InputArgument::REQUIRED, 'PHP code'],
		];
	}
}