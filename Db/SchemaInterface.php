<?php
namespace Asgard\Db;

interface SchemaInterface {
	/**
	 * Empty all tables.
	 */
	public function emptyAll();

	/**
	 * Drop all tables.
	 */
	public function dropAll();

	/**
	 * Create a table.
	 * @param string   $tableName
	 * @param callable $cb
	 */
	public function create($tableName, $cb);

	/**
	 * Empty a table.
	 * @param string $tableName
	 */
	public function emptyTable($tableName);

	/**
	 * Drop a table.
	 * @param string $table
	 */
	public function drop($table);

	/**
	 * Rename a table.
	 * @param string $from
	 * @param string $to
	 */
	public function rename($from, $to);

	/**
	 * Update a table.
	 * @param string   $tableName
	 * @param callable $cb
	 */
	public function table($tableName, $cb);

	/**
	 * Rename a column.
	 * @param string $table
	 * @param string $old
	 * @param string $new
	 * @param string $type
	 */
	public function renameColumn($table, $old, $new, $type=null);

	/**
	 * Return a doctrine connection instance.
	 * @return \Doctrine\DBAL\Connection
	 */
	public function getConn();

	/**
	 * Return a doctrine platform.
	 * @return  Doctrine\DBAL\Platforms\AbstractPlatform
	 */
	public function getPlatform();

	/**
	 * Return a doctrine schema manager.
	 * @return  Doctrine\DBAL\Schema\AbstractSchemaManager
	 */
	public function getSchemaManager();
}