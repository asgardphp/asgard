<?php
namespace Asgard\Migration;

/**
 * Manage the migrations.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface MigrationsManagerInterface {
	/**
	 * Set db dependency.
	 * @param  \Asgard\Db\DBInterface    $db
	 * @return MigrationsManager $this
	 */
	public function setDB(\Asgard\Db\DBInterface $db);

	/**
	 * Set schema dependency.
	 * @param  \Asgard\Db\SchemaInterface $schema
	 * @return MigrationsManager  $this
	 */
	public function setSchema(\Asgard\Db\SchemaInterface $schema);

	/**
	 * Return the tracker instance.
	 * @return Tracker
	 */
	public function getTracker();

	/**
	 * Add a migration file.
	 * @param string $file file path
	 */
	public function add($file);

	/**
	 * Create a new migration from given code.
	 * @param  string $up
	 * @param  string $down
	 * @param  string $name  migration name
	 * @param  string $class entity class
	 * @return string        final migration name
	 */
	public function create($up, $down, $name, $class='\Asgard\Migration\Migration');

	/**
	 * Check if it contains a migration.
	 * @param  string  $migrationName
	 * @return boolean                true if migration exists, false otherwise
	 */
	public function has($migrationName);

	/**
	 * Remove a migration.
	 * @param  string $migrationName
	 */
	public function remove($migrationName);

	/**
	 * Execute a migration.
	 * @param  string  $migrationName
	 * @param  boolean $tracking      true to track the migration status
	 * @return boolean                true for success, otherwise false
	 */
	public function migrate($migrationName, $tracking=false);

	/**
	 * Execute a migration file directly.
	 * @param  string $file file path
	 */
	public function migrateFile($file);

	/**
	 * Execute all migrations.
	 * @param  boolean $tracking true to track the migration status
	 * @return boolean                true for success, otherwise false
	 */
	public function migrateAll($tracking=false);

	/**
	 * Rollback and re-execute all migrations.
	 * @return boolean                true for success, otherwise false
	 */
	public function reset();

	/**
	 * Rollback a migration.
	 * @param  string $migrationName
	 * @return boolean                true for success, otherwise false
	 */
	public function unmigrate($migrationName);

	/**
	 * Rollback the last migration.
	 */
	public function rollback();

	/**
	 * Rollback until a given migration name.
	 * @param  string $migrationName
	 */
	public function rollbackUntil($migrationName);
}