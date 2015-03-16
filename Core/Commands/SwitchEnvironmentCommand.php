<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Environment command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class SwitchEnvironmentCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'env:switch';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Switch the application environment.';
	/**
	 * Kernel dependency.
	 * @var \Asgard\Core\Kernel
	 */
	protected $kernel;

	/**
	 * Constructor.
	 * @param \Asgard\Core\Kernel $kernel
	 */
	public function __construct(\Asgard\Core\Kernel $kernel) {
		$this->kernel = $kernel;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$env = $this->input->getArgument('env');
		\Asgard\File\FileSystem::write($this->kernel['root'].'/storage/environment', $env);
		$this->info('You are now using environment: '.$env);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getArguments() {
		return [
			['env', InputArgument::REQUIRED, 'Environment'],
		];
	}
}