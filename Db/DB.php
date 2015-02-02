<?php
namespace Asgard\Db;

/**
 * Database.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class DB implements DBInterface {
	/**
	 * \PDO instance.
	 * @var \PDO
	 */
	protected $db;
	/**
	 * Configuration.
	 * @var array
	 */
	protected $config;

	protected $conn;

	/**
	 * Constructor.
	 * @param array $config database configuration
	 * @param \PDO  $db database connection
	 * @api
	*/
	public function __construct(array $config, \PDO $db=null) {
		if(!isset($config['driver']))
			$config['driver'] = 'mysql';
		$this->config = $config;
		if($db === null)
			$this->db = $this->getPDO($config);
		else
			$this->db = $db;
		$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * {@inheritDoc}
	 */
	public function dal() {
		return new DAL($this);
	}

	/**
	 * Get the PDO instance.
	 * @param  array $config
	 * @return \PDO
	 */
	protected function getPDO(array $config) {
		$driver = $config['driver'];
		$user = isset($config['user']) ? $config['user']:'root';
		$password = isset($config['password']) ? $config['password']:'';

		switch($driver) {
			case 'pgsql':
				$parameters = 'pgsql:host='.$config['host'].(isset($config['port']) ? ' port='.$config['port']:'').(isset($config['database']) ? ' dbname='.$config['database']:'');
				return new \PDO($parameters, $user, $password);
			case 'mssql':
				$parameters = 'mssql:host='.$config['host'].(isset($config['database']) ? ';dbname='.$config['database']:'');
				return new \PDO($parameters, $user, $password);
			case 'sqlite':
				return new \PDO('sqlite:'.$config['database']);
			default:
				$parameters = 'mysql:host='.$config['host'].(isset($config['port']) ? ';port='.$config['port']:'').(isset($config['database']) ? ';dbname='.$config['database']:'');
				return new \PDO($parameters, $user, $password, [\PDO::MYSQL_ATTR_FOUND_ROWS => true, \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * {@inheritDoc}
	*/
	public function getDB() {
		return $this->db;
	}

	/**
	 * {@inheritDoc}
	*/
	public function query($sql, array $args=[]) {
		return new Query($this->db, $sql, $args);
	}

	/**
	 * {@inheritDoc}
	*/
	public function id() {
		return $this->db->lastInsertId();
	}

	/**
	 * {@inheritDoc}
	*/
	public function beginTransaction() {
		$this->db->beginTransaction();
	}

	/**
	 * {@inheritDoc}
	*/
	public function commit() {
		$this->db->commit();
	}

	/**
	 * {@inheritDoc}
	*/
	public function rollback() {
		$this->db->rollback();
	}


	/**
	 * {@inheritDoc}
	*/
	public function close() {
		unset($this->db);
	}

	public function getSchema() {
		return new Schema($this);
	}

	public function getConn() {
		if(!$this->conn) {
			$driver = $this->getConfig()['driver'];

			if($driver == 'mysql')
				$driver = 'pdo_mysql';
			elseif($driver == 'mssql')
				$driver = 'pdo_sqlsrv';
			elseif($driver == 'pgsql')
				$driver = 'pdo_pgsql';
			elseif($driver == 'sqlite')
				$driver = 'pdo_sqlite';
			else
				throw new \Exception('Invalid driver: '.$driver);

			$c = new \Doctrine\DBAL\Configuration;
			$params = [
				'pdo' => $this->getDB()
			];
			$this->conn = \Doctrine\DBAL\DriverManager::getConnection($params, $c);
		}

		return $this->conn;
	}
}