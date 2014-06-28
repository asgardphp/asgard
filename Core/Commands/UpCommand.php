<?php
namespace Asgard\Core\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpCommand extends \Asgard\Console\Command {
	protected $name = 'up';
	protected $description = 'Bring the application out of maintenance mode';

	protected function execute(InputInterface $input, OutputInterface $output) {
		\Asgard\File\FileSystem::delete($this->getContainer()['kernel']['root'].'/storage/maintenance');
		$this->info('The application is now up.');
	}
}