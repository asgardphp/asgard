<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ExecuteCommand extends \Asgard\Console\Command {
	protected $name = 'execute';
	protected $description = 'Execute PHP code in the application context';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$cmd = $input->getArgument('cmd');
		try {
			if(preg_match('/^dump /', $cmd))
				$cmd = 'var_dump('.substr($cmd, 5).')';
			if(!preg_match('/;$/', $cmd))
				$cmd .= ';';
			eval($cmd);
		} catch(\Exception $e) {
			$output->writeln('<error>'.$e->getMessage().'</error>');
		}
	}

	protected function getArguments() {
		return [
			['cmd', InputArgument::REQUIRED, 'PHP code'],
		];
	}
}