<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Environment command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ShowEnvironmentCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'env:show';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Show the application environment.';
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
		$this->info('You are now using environment: '.$this->kernel->getEnv());
	}
}