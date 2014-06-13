<?php
namespace Asgard\Core\Console;

class UpCommand extends \Asgard\Console\Command {
	protected $name = 'up';
	protected $description = 'Bring the application out of maintenance mode';

	protected function execute() {
		\Asgard\Common\FileManager::unlink($this->getAsgard()['kernel']['root'].'/storage/maintenance');
		$this->output->writeln('<info>The application is now up</info>');
	}
}