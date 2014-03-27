<?php
namespace Asgard\Db;

class DB {
	protected $db;
	protected $config;

	public function __construct($config, $db=null) {
		$this->config = $config;
		if(!$db) {
			$this->db = new \PDO('mysql:host='.$config['host'].';dbname='.$config['database'], 
				$config['user'],
				$config['password'],
				array(\PDO::MYSQL_ATTR_FOUND_ROWS => true, \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8')
			);
		}
		else
			$this->db = $db;
		$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}
	
	public function getDB() {
		return $this->db;
	}
	
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

	public function query($sql, $args=array()) {
		return new Query($this->db, $sql, $args);
	}

	public function id() {
		return $this->db->lastInsertId();
	}

	public function beginTransaction() {
		$this->db->beginTransaction();
	}

	public function commit() {
		$this->db->commit();
	}

	public function rollback() {
		$this->db->rollback();
	}
}