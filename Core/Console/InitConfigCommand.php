<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class InitConfigCommand extends \Asgard\Console\Command {
	protected $name = 'init-config';
	protected $description = 'Initialize the configuration file';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$root = $this->getAsgard()['kernel']['root'];

		if(file_exists($root.'/config/config.php'))
			$output->writeln('File "config/config.php" already exists.');
		else {
			$config = file_get_contents(__DIR__.'/stubs/config.php.stub');
			$key = \Asgard\Utils\Tools::randStr(10);
			$config = str_replace('_KEY_', $key, $config);

			if(\Asgard\Utils\FileManager::put($root.'/config/config.php', $config))
				$output->writeln('<info>Configuration created with success.</info>');
			else
				$output->writeln('<error>Configuration creation failed.</error>');
		}
	}
}