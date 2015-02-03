<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * PHP built-in server command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class ServerCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'server';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Run the php built-in server';

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$host = $this->input->getOption('host');
		$port = $this->input->getOption('port');
		$web = $this->getContainer()['config']['webdir'];

		$this->info("Asgard development server started on http://{$host}:{$port}");
		passthru('"'.PHP_BINARY.'"'." -S $host:$port -t \"$web\"");
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getOptions()
	{
		return array(
			array('host', null, InputOption::VALUE_OPTIONAL, 'The host address.', 'localhost'),
			array('port', null, InputOption::VALUE_OPTIONAL, 'The port.', 8000),
		);
	}
}