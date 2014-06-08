<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ConsoleCommand extends \Asgard\Console\Command {
	protected $name = 'console';
	protected $description = 'Interact with your application';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln('Type "quit" to quit.');

		$dialog = $this->getHelperSet()->get('dialog');

		$cmd = $dialog->ask($output, '>');
		while($cmd != "quit") {
			try {
				if(preg_match('/^dump /', $cmd))
					$cmd = 'var_dump('.substr($cmd, 5).')';
				if(!preg_match('/;$/', $cmd))
					$cmd .= ';';
				eval($cmd);
			} catch(\Exception $e) {
				$output->writeln('<error>'.$e->getMessage().'</error>');
			}

			$cmd = $dialog->ask($output, '>');
		}
		$output->writeln('Quiting..');
	}
}