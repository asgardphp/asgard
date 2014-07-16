<?php
namespace Asgard\Config\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends \Asgard\Console\Command {
	protected $name = 'config:init';
	protected $description = 'Initialize the configuration file';
	protected $dir;

	public function __construct($dir) {
		$this->dir = $dir;
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->initConfig('config.yml');
		$this->initConfig('config_dev.yml');
		$this->initConfig('config_prod.yml');
		$this->initConfig('config_test.yml');
	}

	protected function initConfig($file) {
		if(file_exists($this->dir.'/'.$file)) {
			if(!$this->confirm('Do you want to override "'.$file.'"?'))
				return;
		}

		$config = file_get_contents(__DIR__.'/stubs/'.$file.'.stub');
		$key = \Asgard\Common\Tools::randStr(10);
		$config = str_replace('_KEY_', $key, $config);

		if(\Asgard\File\FileSystem::write($this->dir.'/'.$file, $config))
			$this->info('Configuration file "'.$file.'" created with success.');
		else
			$this->error('Configuration file "'.$file.'" creation failed.');
	}
}