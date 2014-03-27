<?php
namespace Asgard\Db\Cli;

class DbController extends \Asgard\Cli\CLIController {
	/**
	@Shortcut('db:init')
	@Usage('db:init')
	@Description('Setup the database configuration')
	*/
	public function configAction($request) {
		ob_end_clean();

		if(file_exists('config/database.php'))
			die('File "config/database.php" already exists.');

		echo 'Database host ("localhost"): ';
		if(!$host = trim(fgets(STDIN)))
			$host = 'localhost';
		echo 'Database user ("root"): ';
		if(!$user = trim(fgets(STDIN)))
			$user = 'root';
		echo 'Database password (""): ';
		$password = trim(fgets(STDIN));
		echo 'Database name ("asgard"): ';
		if(!$name = trim(fgets(STDIN)))
			$name = 'asgard';
		echo 'Database prefix (""): ';
		$prefix = trim(fgets(STDIN));

		$config = file_get_contents(__DIR__.'/database.php.sample');

		$config = str_replace('_HOST_', $host, $config);
		$config = str_replace('_USER_', $user, $config);
		$config = str_replace('_PASSWORD_', $password, $config);
		$config = str_replace('_NAME_', $name, $config);
		$config = str_replace('_PREFIX_', $prefix, $config);

		if(\Asgard\Utils\FileManager::put('config/database.php', $config) !== false)
			echo 'Database configuration created with success.';
		else
			echo 'Database configuration creation failed.';
	}
}