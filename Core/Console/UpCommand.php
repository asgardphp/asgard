<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UpCommand extends \Asgard\Console\Command {
	protected $name = 'up';
	protected $description = 'Bring the application out of maintenance mode';

	protected function execute(InputInterface $input, OutputInterface $output) {
		\Asgard\Common\FileManager::unlink($this->getAsgard()['kernel']->getRoot().'/storage/maintenance');
		$output->writeln('<info>The application is now up</info>');
	}
}