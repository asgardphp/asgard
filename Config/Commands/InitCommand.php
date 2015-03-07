<?php
namespace Asgard\Config\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Init config command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class InitCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'config:init';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Initialize the configuration file';
	/**
	 * Configuration directory.
	 * @var string
	 */
	protected $dir;

	/**
	 * Constructor.
	 * @param string $dir
	 */
	public function __construct($dir) {
		$this->dir = $dir;
		parent::__construct();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$this->initConfig('key.yml');
	}

	/**
	 * Initialize a configuration file.
	 * @param  string $file
	 */
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