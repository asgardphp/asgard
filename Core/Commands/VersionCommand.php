<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VersionCommand extends \Asgard\Console\Command {
	protected $name = 'version';
	protected $description = 'Dislay this application version';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->output->writeln('Asgard Framework Version '.$this->getContainer()['kernel']->getVersion());
	}
}