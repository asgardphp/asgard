<?php
namespace Asgard\Db;

class MySQL {
	protected $config;

	public function __construct($config) {
		$this->config = $config;
	}

	/**
	 * Import an SQL file
	 * 
	 * @param String file SQL file path
	 * 
	 * @throws \Exception When file is not accessible
	 * 
	 * @return Integer 1 for success, 0 for failure
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