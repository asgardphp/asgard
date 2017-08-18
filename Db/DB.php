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
	protected $pdo;
	/**
	 * Configuration.
	 * @var array
	 */
	protected $config;

	protected $conn;

	/**
	 * Constructor.
	 * @param array $config database configuration
	 * @param \PDO  $pdo    database connection
	 * @api
	*/
	public function __construct(array $config, \PDO $pdo=null) {
		if(!isset($config['driver']))
			$config['driver'] = 'mysql';
		$this->config = $config;
		if($pdo !== null) {
			$this->pdo = $pdo;
			$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function dal() {
		return new DAL($this);
	}

	/**
	 * Build the PDO instance.
	 * @param  array  $config
	 */
	public function buildPDO(array $config=null) {
		if(!$config)
			$config = $this->config;
		$driver = $config['driver'];
		$user = isset($config['user']) ? $config['user']:'root';
		$password = isset($config['password']) ? $config['password']:'';
		$database = isset($config['database']) ? $config['database']:null;

		switch($driver) {
			case 'pgsql':
				$parameters = 'pgsql:host='.$config['host'].(isset($config['port']) ? ' port='.$config['port']:'').($database ? ' dbname='.$database:'');
				$this->pdo = new \PDO($parameters, $user, $password);
				break;
			case 'mssql':
				$parameters = 'mssql:host='.$config['host'].($database ? ';dbname='.$database:'');
				$this->pdo = new \PDO($parameters, $user, $password);
				break;
			case 'sqlite':
				$pdo = $this->pdo = new \PDO('sqlite:'.$database);
	
				require_once __DIR__.'/sqlite_functions.php';

				break;
			default:
				$parameters = 'mysql:host='.$config['host'].(isset($config['port']) ? ';port='.$config['port']:'').($database ? ';dbname='.$database:'');
				$this->pdo = new \PDO($parameters, $user, $password, [\PDO::MYSQL_ATTR_FOUND_ROWS => true, \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
		}
		$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * Get the PDO instance.
	 * @return \PDO
	 */
	public function getPDO() {
		if(!isset($this->pdo))
			$this->buildPDO();

		return $this->pdo;
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
	public function query($sql, array $args=[]) {
		return new Query($this->getPDO(), $sql, $args);
	}

	/**
	 * {@inheritDoc}
	*/
	public function id() {
		return $this->getPDO()->lastInsertId();
	}

	/**
	 * {@inheritDoc}
	*/
	public function inTransaction() {
		return $this->getPDO()->inTransaction();
	}

	/**
	 * {@inheritDoc}
	*/
	public function beginTransaction() {
		$this->getPDO()->beginTransaction();
	}

	/**
	 * {@inheritDoc}
	*/
	public function commit() {
		$this->getPDO()->commit();
	}

	/**
	 * {@inheritDoc}
	*/
	public function rollback() {
		$this->getPDO()->rollback();
	}


	/**
	 * {@inheritDoc}
	*/
	public function close() {
		unset($this->pdo);
	}

	public function getSchema() {
		return new Schema($this);
	}

	public function getConn() {
		if(!$this->conn) {
			$c = new \Doctrine\DBAL\Configuration;
			$params = [
				'pdo' => $this->getPDO()
			];
			$this->conn = \Doctrine\DBAL\DriverManager::getConnection($params, $c);
		}

		return $this->conn;
	}
}