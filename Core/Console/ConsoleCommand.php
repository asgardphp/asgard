<?php
namespace Asgard\Core\Console;

class ConsoleCommand extends \Asgard\Console\Command {
	protected $name = 'console';
	protected $description = 'Interact with your application';

	protected function execute() {
		$this->output->writeln('Type "quit" to quit.');

		$dialog = $this->getHelperSet()->get('dialog');

		$cmd = $dialog->ask($this->output, '>');
		while($cmd != "quit") {
			try {
				if(preg_match('/^dump /', $cmd))
					$cmd = 'var_dump('.substr($cmd, 5).')';
				if(!preg_match('/;$/', $cmd))
					$cmd .= ';';
				eval($cmd);
			} catch(\Exception $e) {
				$this->output->writeln('<error>'.$e->getMessage().'</error>');
			}

			$cmd = $dialog->ask($this->output, '>');
		}
		$this->output->writeln('Quiting..');
	}
}