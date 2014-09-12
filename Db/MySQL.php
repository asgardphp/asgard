<?php
namespace Asgard\Db;

/**
 * Util to import and dump mysql files.
 */
class MySQL {
	/**
	 * Configuration.
	 * @var array
	 */
	protected $config;

	/**
	 * Constructor.
	 * @param array $config
	 */
	public function __construct($config) {
		$this->config = $config;
	}

	/**
	 * Import an SQL file.
	 * @param string $file SQL file path.
	 * @throws \Exception When file is not accessible.
	 * @return integer 1 for success, 0 for failure.
	*/
	public function import($src) {
		if(!file_exists(realpath($src)))
			throw new \Exception('File '.$src.' does not exist.');
		$host = $this->config['host'];
		$user = $this->config['user'];
		$pwd = $this->config['password'];
		$db = $this->config['database'];
		$cmd = 'mysql -h '.$host.' -u '.$user.($pwd ? ' -p'.$pwd:'').' '.$db.' < '.realpath($src);
		$process = proc_open($cmd,
			[
			   0 => ["pipe", "r"],
			   1 => ["pipe", "w"],
			   2 => ["pipe", "w"],
			],
			$pipes
		);
		return proc_close($process) === 0;
	}

	/**
	 * Dump an SQL file.
	 * @param  string $dst
	 * @return integer 1 for success, 0 for failure.
	 */
	public function dump($dst) {
		$host = $this->config['host'];
		$user = $this->config['user'];
		$pwd = $this->config['password'];
		$db = $this->config['database'];

		\Asgard\File\FileSystem::mkdir(dirname($dst));
		$cmd = 'mysqldump --user='.$user.' --password='.$pwd.' --host='.$host.' '.$db.' > '.$dst;
		$process = proc_open($cmd,
			[
			   0 => ["pipe", "r"],
			   1 => ["pipe", "w"],
			   2 => ["pipe", "w"],
			],
			$pipes
		);
		return proc_close($process) === 0;
	}
}