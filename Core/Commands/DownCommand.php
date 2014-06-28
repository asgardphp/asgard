<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownCommand extends \Asgard\Console\Command {
	protected $name = 'down';
	protected $description = 'Put the application into maintenance mode';

	protected function execute(InputInterface $input, OutputInterface $output) {
		\Asgard\File\FileSystem::write($this->getContainer()['kernel']['root'].'/storage/maintenance', '');
		$this->info('The application is now down');
	}
}