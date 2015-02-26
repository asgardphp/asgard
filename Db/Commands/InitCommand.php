<?php
namespace Asgard\Db\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Init the database command.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class InitCommand extends \Asgard\Console\Command {
	/**
	 * {@inheritDoc}
	 */
	protected $name = 'db:init';
	/**
	 * {@inheritDoc}
	 */
	protected $description = 'Initialize the database configuration';
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

	protected function ask($question, $default=null) {
		$helper = $this->getHelperSet()->get('question');
		$question = new \Symfony\Component\Console\Question\Question($question, $default);
		return $helper->ask($this->input, $this->output, $question);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
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
			$driver = $this->ask('Driver mysql/pgsql/mssql/sqlite ("mysql"): ', 'mysql');
		if($driver === 'sqlite')
			$host = $user = $password = null;
		else {
			$host = $this->ask('Database host ("localhost"): ', 'localhost');
			$user = $this->ask('Database user ("root"): ', 'root');
			$password = $this->ask('Database password (""): ', '');
		}
		$name = $this->ask('Database name ("asgard"): ', 'asgard');
		$prefix = $this->ask('Database prefix (""): ');

		$config = file_get_contents(__DIR__.'/stubs/database.yml.stub');

		$config = str_replace('_DRIVER_', $driver, $config);
		$config = str_replace('_HOST_', $host, $config);
		$config = str_replace('_USER_', $user, $config);
		$config = str_replace('_PASSWORD_', $password, $config);
		$config = str_replace('_NAME_', $name, $config);
		$config = str_replace('_PREFIX_', $prefix, $config);

		try {
			if($driver == 'sqlite') {
				if(file_exists($name))
					$this->comment('The database "'.$name.'" already exists.');
				else {
					\Asgard\File\FileSystem::write($name, '');
					$this->info('Database created.');
				}
			}
			else {
				$db = new \Asgard\Db\DB([
					'driver' => $driver,
					'host' => $host,
					'user' => $user,
					'password' => $password,
					'name' => $name,
				]);
				$db->getSchema()->getSchemaManager()->createDatabase($name);
				$this->info('Database created.');
			}
		} catch(\Exception $e) {
			$this->comment('Database could not be created.');
		}

		if(\Asgard\File\FileSystem::write($this->dir.'/'.$file, $config))
			$this->info('Database configuration created with success.');
		else
			$this->error('Database configuration creation failed.');
	}
}