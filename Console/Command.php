<?php
namespace Asgard\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class Command extends \Symfony\Component\Console\Command\Command {
	protected $name;
	protected $description;
	protected $input;
	protected $output;
	
	/**
	 * Constructor.
	*/
	public function __construct() {
		parent::__construct($this->name);
		$this->setDescription($this->description);

		$this->specifyParameters();
	}
	
	/**
	 * Returns the services container.
	 * 
	 * @return \Asgard\Container\Container
	*/
	protected function getContainer() {
		return $this->getApplication()->getContainer();
	}
	
	/**
	 * Sets options and arguments of the command.
	*/
	protected function specifyParameters() {
		foreach ($this->getArguments() as $arguments)
			call_user_func_array([$this, 'addArgument'], $arguments);

		foreach ($this->getOptions() as $options)
			call_user_func_array([$this, 'addOption'], $options);
	}
	
	/**
	 * Command options.
	 * 
	 * @return array
	*/
	protected function getOptions() {
		return [];
	}
	
	/**
	 * Command arguments.
	 * 
	 * @return array
	*/
	protected function getArguments() {
		return [];
	}

	public function run(InputInterface $input, OutputInterface $output) {
		$this->input = $input;
		$this->output = $output;

		return parent::run($input, $output);
	}

	public function call($command, array $arguments = []) {
		$instance = $this->getApplication()->find($command);
		$arguments['command'] = $command;

		return $instance->run(new ArrayInput($arguments), $this->output);
	}

	public function callSilent($command, array $arguments = []) {
		$instance = $this->getApplication()->find($command);
		$arguments['command'] = $command;

		return $instance->run(new ArrayInput($arguments), new NullOutput);
	}

	public function confirm($questionStr) {
		$helper = $this->getHelperSet()->get('question');
		$question = new ConfirmationQuestion($questionStr, false);

		return $helper->ask($this->input, $this->output, $question);
	}

	public function info($msg) {
		$this->output->writeln('<info>'.$msg.'</info>');
	}

	public function error($msg) {
		$this->output->writeln('<error>'.$msg.'</error>');
	}

	public function comment($msg) {
		$this->output->writeln('<comment>'.$msg.'</comment>');
	}

	public function question($msg) {
		$this->output->writeln('<question>'.$msg.'</question>');
	}
}