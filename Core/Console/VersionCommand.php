<?php
namespace Asgard\Http\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class VersionCommand extends \Asgard\Console\Command {
	protected $name = 'version';
	protected $description = 'Dislay this application version';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln('Asgard Framework Version '.$this->getAsgard()['kernel']->getVersion());
	}
}