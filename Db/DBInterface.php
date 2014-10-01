<?php
namespace Asgard\Db;

/**
 * Database.
 */
interface DBInterface {
	/**
	 * Return a DAL instance.
	 * @return DAL
	 */
	public function getDAL();

	/**
	 * Return the configuration.
	 * @return array
	 */
	public function getConfig();
	
	/**
	 * Return the database instance.
	 * @return \PDO
	*/
	public function getDB();
	
	/**
	 * Execute an SQL query.
	 * @param string $sql SQL query
	 * @param array $args SQL parameters
	 * @return Asgard\Db\Query Query object
	*/
	public function query($sql, array $args=[]);
	
	/**
	 * Return the last inserted id.
	 * @return integer Last inserted id
	*/
	public function id();
	
	/**
	 * Start a new SQL transaction.
	*/
	public function beginTransaction();
	
	/**
	 * Commit the SQL transaction.
	*/
	public function commit();
	
	/**
	 * Roll back the SQL transaction.
	*/
	public function rollback();
}