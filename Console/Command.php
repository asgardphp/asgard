<?php
namespace Asgard\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command parent class.
 * @author Michel Hognerud <michel@hognerud.com>
 * @api
 */
class Command extends \Symfony\Component\Console\Command\Command {
	/**
	 * Command name.
	 * @var string
	 */
	protected $name;
	/**
	 * Command description.
	 * @var string
	 */
	protected $description;
	/**
	 * Input instance
	 * @var \Symfony\Component\Console\Input\InputInterface
	 */
	protected $input;
	/**
	 * Output instance
	 * @var \Symfony\Component\Console\Output\OutputInterface
	 */
	protected $output;

	/**
	 * Constructor.
	 * @api
	*/
	public function __construct() {
		parent::__construct($this->name);
		$this->setDescription($this->description);

		$this->specifyParameters();
	}

	/**
	 * Return the services container.
	 * @return \Asgard\Container\ContainerInterface
	*/
	protected function getContainer() {
		if($this->getApplication() instanceof \Asgard\Console\Application)
			return $this->getApplication()->getContainer();
	}

	/**
	 * Set options and arguments of the command.
	*/
	protected function specifyParameters() {
		foreach ($this->getArguments() as $arguments)
			call_user_func_array([$this, 'addArgument'], $arguments);

		foreach ($this->getOptions() as $options)
			call_user_func_array([$this, 'addOption'], $options);
	}

	/**
	 * Command options.
	 * @return array
	*/
	protected function getOptions() {
		return [];
	}

	/**
	 * Command arguments.
	 * @return array
	*/
	protected function getArguments() {
		return [];
	}

	/**
	 * Run the command.
	 * @param  InputInterface  $input
	 * @param  OutputInterface $output
	 * @return integer
	 */
	public function run(InputInterface $input, OutputInterface $output) {
		$this->input = $input;
		$this->output = $output;

		return parent::run($input, $output);
	}

	/**
	 * Call another command.
	 * @param  string $command
	 * @param  array $arguments
	 * @return integer
	 */
	public function call($command, array $arguments = []) {
		$instance = $this->getApplication()->find($command);
		$arguments['command'] = $command;

		return $instance->run(new ArrayInput($arguments), $this->output);
	}

	/**
	 * Call another command silently.
	 * @param  string $command
	 * @param  array $arguments
	 * @return integer
	 */
	public function callSilent($command, array $arguments = []) {
		$instance = $this->getApplication()->find($command);
		$arguments['command'] = $command;

		return $instance->run(new ArrayInput($arguments), new NullOutput);
	}

	/**
	 * Prompt user for confirmation.
	 * @param  string $questionStr
	 * @return boolean
	 */
	public function confirm($questionStr) {
		$helper = $this->getHelperSet()->get('question');
		$question = new ConfirmationQuestion($questionStr.' (yes/no)', false);

		return $helper->ask($this->input, $this->output, $question);
	}

	/**
	 * Output information message.
	 * @param  string $msg
	 */
	public function info($msg) {
		$this->output->writeln('<info>'.$msg.'</info>');
	}

	/**
	 * Output error message.
	 * @param  string $msg
	 */
	public function error($msg) {
		$this->output->writeln('<error>'.$msg.'</error>');
	}

	/**
	 * Output comment message.
	 * @param  string $msg
	 */
	public function comment($msg) {
		$this->output->writeln('<comment>'.$msg.'</comment>');
	}

	/**
	 * Output question message.
	 * @param  string $msg
	 */
	public function question($msg) {
		$this->output->writeln('<question>'.$msg.'</question>');
	}
}