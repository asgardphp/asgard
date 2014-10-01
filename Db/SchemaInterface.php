<?php
namespace Asgard\Db;

interface SchemaInterface {
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
	 * Drop a column.
	 * @param string $table
	 * @param string $col
	 */
	public function dropColumn($table, $col);
	
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
}