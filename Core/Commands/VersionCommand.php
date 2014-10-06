<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Show version command.
 */
class VersionCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'version';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Dislay this application version';

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output->writeln('Asgard Framework Version '.$this->getContainer()['kernel']->getVersion());
	}
}