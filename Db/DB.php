<?php
namespace Asgard\Db;

class DB {
	protected $db;
	protected $config;
	
	/**
	 * Constructor
	 * 
	 * @param array config database configuration
	 * @param \PDO db database connection
	*/
	public function __construct(array $config, \PDO $db=null) {
		$this->config = $config;
		if(!$db)
			$this->db = $this->getPDO($config);
		else
			$this->db = $db;
		$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	protected function getPDO($config) {
		$driver = isset($config['driver']) ? $config['driver']:'mysql';
		$user = isset($config['user']) ? $config['user']:'root';
		$password = isset($config['password']) ? $config['password']:'';

		switch($driver) {
			case 'mysql':
				$parameters = 'mysql:host='.$config['host'].(isset($config['port']) ? ';port='.$config['port']:'').(isset($config['database']) ? ';dbname='.$config['database']:'');
				return new \PDO($parameters, $user, $password, array(\PDO::MYSQL_ATTR_FOUND_ROWS => true, \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
			case 'pgsql':
				$parameters = 'pgsql:host='.$config['host'].(isset($config['port']) ? ' port='.$config['port']:'').(isset($config['database']) ? ' dbname='.$config['database']:'');
				return new \PDO($parameters, $user, $password);
			case 'mssql':
				$parameters = 'mssql:host='.$config['host'].(isset($config['database']) ? ';dbname='.$config['database']:'');
				return new \PDO($parameters, $user, $password);
			case 'sqlite':
				return new \PDO('sqlite:'.$config['database']);

		}
	}

	public function getConfig() {
		return $this->config;
	}
	
	/**
	 * sdsf
	 * 
	 * @return sdfsd sdfds
	*/
	public function getDB() {
		return $this->db;
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
			array(
			   0 => array("pipe", "r"),
			   1 => array("pipe", "w"),
			   2 => array("pipe", "w"),
			),
			$pipes
		);
		return proc_close($process) === 0;
	}

	public function dump($dst) {
		$return = null;
		$host = $this->config['host'];
		$user = $this->config['user'];
		$pwd = $this->config['password'];
		$db = $this->config['database'];

		\Asgard\Utils\FileManager::mkdir(dirname($dst));
		$cmd = 'mysqldump --user='.$user.' --password='.$pwd.' --host='.$host.' '.$db.' > '.$dst;
		$process = proc_open($cmd,
			array(
			   0 => array("pipe", "r"),
			   1 => array("pipe", "w"),
			   2 => array("pipe", "w"),
			),
			$pipes
		);
		return proc_close($process) === 0;
	}
	
	/**
	 * Executes an SQL query
	 * 
	 * @param String sql SQL query
	 * @param array args SQL parameters
	 * 
	 * @return Asgard\Db\Query Query object
	*/
	public function query($sql, array $args=array()) {
		return new Query($this->db, $sql, $args);
	}
	
	/**
	 * Returns the last inserted id
	 * 
	 * @return Integer Last inserted id
	*/
	public function id() {
		return $this->db->lastInsertId();
	}
	
	/**
	 * Starts a new SQL transaction
	*/
	public function beginTransaction() {
		$this->db->beginTransaction();
	}
	
	/**
	 * Commits the SQL transaction
	*/
	public function commit() {
		$this->db->commit();
	}
	
	/**
	 * Rolls back the SQL transaction
	*/
	public function rollback() {
		$this->db->rollback();
	}
}