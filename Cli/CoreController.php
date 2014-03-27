<?php
namespace Asgard\Core\Cli;

class CoreController extends \Asgard\Cli\CLIController {
	/**
	@Shortcut('config:init')
	@Usage('config:init')
	@Description('Setup the general configuration')
	*/
	public function configAction($request) {
		ob_end_clean();

		if(file_exists('config/config.php'))
			die('File "config/config.php" already exists.');

		$config = file_get_contents(__DIR__.'/config.php.sample');
		$key = \Asgard\Utils\Tools::randStr(10);
		d($key);
		$config = str_replace('_KEY_', $key, $config);

		if(\Asgard\Utils\FileManager::put('config/config.php', $config) !== false)
			echo 'Configuration created with success.';
		else
			echo 'Configuration creation failed.';
	}
}