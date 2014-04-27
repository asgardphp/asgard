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
	public function __construct($config, $db=null) {
		$this->config = $config;
		if(!$db) {
			$this->db = new \PDO('mysql:host='.$config['host'].(isset($config['database']) ? ';dbname='.$config['database']:''),
				$config['user'],
				$config['password'],
				array(\PDO::MYSQL_ATTR_FOUND_ROWS => true, \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8')
			);
		}
		else
			$this->db = $db;
		$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
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
	public function import($file) {
	
		if(!file_exists(realpath($file)))
			throw new \Exception('File '.$file.' does not exist.');
		$host = $this->config['host'];
		$user = $this->config['user'];
		$pwd = $this->config['password'];
		$db = $this->config['database'];
		$cmd = 'mysql -h '.$host.' -u '.$user.($pwd ? ' -p'.$pwd:'').' '.$db.' < '.realpath($file);
		return exec($cmd);
	}
	
	/**
	 * Executes an SQL query
	 * 
	 * @param String sql SQL query
	 * @param array args SQL parameters
	 * 
	 * @return Asgard\Db\Query Query object
	*/
	public function query($sql, $args=array()) {
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