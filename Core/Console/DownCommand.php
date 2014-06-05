<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DownCommand extends \Asgard\Console\Command {
	protected $name = 'down';
	protected $description = 'Put the application into maintenance mode';

	protected function execute(InputInterface $input, OutputInterface $output) {
		\Asgard\Utils\FileManager::put($this->getAsgard()['kernel']->getRoot().'/storage/maintenance', '');
		$output->writeln('<info>The application is now down</info>');
	}
}