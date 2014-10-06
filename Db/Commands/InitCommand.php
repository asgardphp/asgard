<?php
namespace Asgard\Db\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Init the database command.
 */
class InitCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'db:init';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Initialize the database';
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
		$dialog = $this->getHelperSet()->get('dialog');

		$env = $this->input->getOption('env');
		if(!$env)
			$file = 'database.yml';
		else
			$file = 'database_'.$env.'.yml';

		if(file_exists($this->dir.'/'.$file)) {
			if(!$this->confirm('Do you want to override "'.$file.'"?'))
				return;
		}

		while(!isset($driver) || !in_array($driver, ['mysql', 'pgsql', 'mssql', 'sqlite']))
			$driver = $dialog->ask($this->output, 'Driver mysql/pgsql/mssql/sqlite ("mysql"): ', 'mysql');
		if($driver === 'sqlite')
			$host = $user = $password = null;
		else {
			$host = $dialog->ask($this->output, 'Database host ("localhost"): ', 'localhost');
			$user = $dialog->ask($this->output, 'Database user ("root"): ', 'root');
			$password = $dialog->ask($this->output, 'Database password (""): ', '');
		}
		$name = $dialog->ask($this->output, 'Database name ("asgard"): ', 'asgard');
		$prefix = $dialog->ask($this->output, 'Database prefix (""): ');

		$config = file_get_contents(__DIR__.'/stubs/database.yml.stub');

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
			$this->comment('Database already exist.');
		} catch(\PDOException $e) {
			try {
				$db = new \Asgard\Db\DB([
					'driver' => $driver,
					'host' => $host,
					'user' => $user,
					'password' => $password,
				]);
				$db->query('CREATE DATABASE `'.$name.'` CHARACTER SET utf8 COLLATE utf8_general_ci');
			} catch(\PDOException $e) {
				$this->error('The database could not be created.');
			}
		}

		if(\Asgard\File\FileSystem::write($this->dir.'/'.$file, $config))
			$this->info('Database configuration created with success.');
		else
			$this->error('Database configuration creation failed.');
	}
}