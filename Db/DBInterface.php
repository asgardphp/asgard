<?php
namespace Asgard\Db;

/**
 * Database.
 * @author Michel Hognerud <michel@hognerud.com>
 * @api
 */
interface DBInterface {
	/**
	 * Return a DAL instance.
	 * @return DAL
	 * @api
	 */
	public function getDAL();

	/**
	 * Return the configuration.
	 * @return array
	 * @api
	 */
	public function getConfig();

	/**
	 * Return the database instance.
	 * @return \PDO
	 * @api
	*/
	public function getDB();

	/**
	 * Execute an SQL query.
	 * @param  string $sql SQL query
	 * @param  array  $args SQL parameters
	 * @return Query  Query object
	 * @api
	*/
	public function query($sql, array $args=[]);

	/**
	 * Return the last inserted id.
	 * @return integer Last inserted id
	 * @api
	*/
	public function id();

	/**
	 * Start a new SQL transaction.
	 * @api
	*/
	public function beginTransaction();

	/**
	 * Commit the SQL transaction.
	 * @api
	*/
	public function commit();

	/**
	 * Roll back the SQL transaction.
	 * @api
	*/
	public function rollback();
}