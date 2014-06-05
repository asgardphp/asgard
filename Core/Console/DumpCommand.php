<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DumpCommand extends \Asgard\Console\Command {
	protected $name = 'dump';
	protected $description = 'Make a backup of a user-content folder';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$dst = $input->getArgument('dst') ? $input->getArgument('dst') : $this->getAsgard()['kernel']->getRoot().'/storage/dumps/files/'.time().'.zip';
		$src = $input->getArgument('src');
		\Asgard\Utils\FileManager::mkdir(dirname($dst));
		if(\Asgard\Utils\Zip::zip($src, $dst))
			$output->writeln('<info>Files have been copied with success.</info>');
		else
			$output->writeln('<error>Files could not be copied.</error>');
	}

	protected function getArguments() {
		return array(
			array('src', InputArgument::REQUIRED, 'Source folder'),
			array('dst', InputArgument::OPTIONAL, 'Destination file'),
		);
	}
}