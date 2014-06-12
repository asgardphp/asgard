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

		$this->initConfig($root, 'config.php', $input, $output);
		$this->initConfig($root, 'config_dev.php', $input, $output);
		$this->initConfig($root, 'config_prod.php', $input, $output);
		$this->initConfig($root, 'config_test.php', $input, $output);
	}

	protected function initConfig($root, $file, $input, $output) {
		if(file_exists($root.'/config/'.$file)) {
			if(!$this->confirm('Do you want to override "'.$file.'"?'))
				return;
		}

		$config = file_get_contents(__DIR__.'/stubs/'.$file.'.stub');
		$key = \Asgard\Common\Tools::randStr(10);
		$config = str_replace('_KEY_', $key, $config);

		if(\Asgard\Common\FileManager::put($root.'/config/'.$file, $config))
			$output->writeln('<info>Configuration file "'.$file.'" created with success.</info>');
		else
			$output->writeln('<error>Configuration file "'.$file.'" creation failed.</error>');
	}
}