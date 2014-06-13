<?php
namespace Asgard\Core\Console;

class DownCommand extends \Asgard\Console\Command {
	protected $name = 'down';
	protected $description = 'Put the application into maintenance mode';

	protected function execute() {
		\Asgard\Common\FileManager::put($this->getAsgard()['kernel']['root'].'/storage/maintenance', '');
		$this->output->writeln('<info>The application is now down</info>');
	}
}