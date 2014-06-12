<?php
namespace Asgard\Core\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class InitCommand extends \Asgard\Console\Command {
	protected $name = 'db:init';
	protected $description = 'Initialize the database';

	protected function execute(InputInterface $input, OutputInterface $output) {
		$root = $this->getAsgard()['kernel']['root'];
		$dialog = $this->getHelperSet()->get('dialog');

		$env = $input->getOption('env');
		if(!$env)
			$file = 'config/database.php';
		else
			$file = 'config/database_'.$env.'.php';

		if(file_exists($root.'/'.$file)) {
			if(!$this->confirm('Do you want to override "'.$file.'"?'))
				return;
		}

		while(!isset($driver) || !in_array($driver, ['mysql', 'pgsql', 'mssql', 'sqlite']))
			$driver = $dialog->ask($output, 'Driver mysql/pgsql/mssql/sqlite ("mysql"): ', 'mysql');
		if($driver === 'sqlite')
			$host = $user = $password = null;
		else {
			$host = $dialog->ask($output, 'Database host ("localhost"): ', 'localhost');
			$user = $dialog->ask($output, 'Database user ("root"): ', 'root');
			$password = $dialog->ask($output, 'Database password (""): ', '');
		}
		$name = $dialog->ask($output, 'Database name ("asgard"): ', 'asgard');
		$prefix = $dialog->ask($output, 'Database prefix (""): ');

		$config = file_get_contents(__DIR__.'/stubs/database.php.stub');

		$config = str_replace('_DRIVER_', $driver, $config);
		$config = str_replace('_HOST_', $host, $config);
		$config = str_replace('_USER_', $user, $config);
		$config = str_replace('_PASSWORD_', $password, $config);
		$config = str_replace('_NAME_', $name, $config);
		$config = str_replace('_PREFIX_', $prefix, $config);

		try {
			$db = new \Asgard\Db\DB([
				'driver' => $driver,
				'host' => $host,
				'user' => $user,
				'password' => $password,
				'database' => $name,
			]);
		} catch(\PDOException $e) {
			try {
				$db = new \Asgard\Db\DB([
					'driver' => $driver,
					'host' => $host,
					'user' => $user,
					'password' => $password,
				]);
				$db->query('CREATE DATABASE `'.$name.'`');
			} catch(\PDOException $e) {
				$output->writeln('<error>The database could not be created.</error>');
			}
		}

		if(\Asgard\Common\FileManager::put($root.'/'.$file, $config))
			$output->writeln('<info>Database configuration created with success.</info>');
		else
			$output->writeln('<error>Database configuration creation failed.</error>');
	}

	protected function getOptions() {
		return [
			['env', null, InputOption::VALUE_NONE, 'Configuration environment.', null],
		];
	}
}