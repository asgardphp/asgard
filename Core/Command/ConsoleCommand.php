<?php
namespace Asgard\Core\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Console command.
 * @author Michel Hognerud <michel@hognerud.com>
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
		$container = $this->getContainer();

		$dialog = $this->getHelperSet()->get('question');
		$question = new Question('>', false);

		$cmd = $dialog->ask($this->input, $this->output, $question);
		while($cmd != "quit") {
			try {
				if(preg_match('/^dump /', $cmd))
					$cmd = 'var_dump('.substr($cmd, 5).')';
				if(!preg_match('/;$/', $cmd))
					$cmd .= ';';
				ob_start();
				eval($cmd);
				$_res = ob_get_clean();
				if($_res)
					echo $_res."\n";
			} catch(\Exception $e) {
				ob_get_clean();
				$this->error($e->getMessage());
			}

			$cmd = $dialog->ask($this->input, $this->output, $question);
		}
		$this->output->writeln('Quiting..');
	}
}