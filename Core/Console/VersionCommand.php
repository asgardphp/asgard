<?php
namespace Asgard\Http\Console;

class VersionCommand extends \Asgard\Console\Command {
	protected $name = 'version';
	protected $description = 'Dislay this application version';

	protected function execute() {
		$this->output->writeln('Asgard Framework Version '.$this->getAsgard()['kernel']->getVersion());
	}
}