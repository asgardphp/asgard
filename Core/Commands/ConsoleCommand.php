<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console command.
 */
class ConsoleCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'console';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Interact with your application';

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
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
				$this->error($e->getMessage());
			}

			$cmd = $dialog->ask($this->output, '>');
		}
		$this->output->writeln('Quiting..');
	}
}