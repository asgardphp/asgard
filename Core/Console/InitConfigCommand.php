<?php
namespace Asgard\Core\Console;

class InitConfigCommand extends \Asgard\Console\Command {
	protected $name = 'init-config';
	protected $description = 'Initialize the configuration file';

	protected function execute() {
		$root = $this->getAsgard()['kernel']['root'];

		$this->initConfig($root, 'config.php');
		$this->initConfig($root, 'config_dev.php');
		$this->initConfig($root, 'config_prod.php');
		$this->initConfig($root, 'config_test.php');
	}

	protected function initConfig($root, $file) {
		if(file_exists($root.'/config/'.$file)) {
			if(!$this->confirm('Do you want to override "'.$file.'"?'))
				return;
		}

		$config = file_get_contents(__DIR__.'/stubs/'.$file.'.stub');
		$key = \Asgard\Common\Tools::randStr(10);
		$config = str_replace('_KEY_', $key, $config);

		if(\Asgard\Common\FileManager::put($root.'/config/'.$file, $config))
			$this->output->writeln('<info>Configuration file "'.$file.'" created with success.</info>');
		else
			$this->output->writeln('<error>Configuration file "'.$file.'" creation failed.</error>');
	}
}