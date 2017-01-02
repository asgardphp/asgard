<?php
namespace Asgard\Migration;

/**
 * Migration class for database
 * @author Michel Hognerud <michel@hognerud.com>
 */
abstract class DBMigration extends Migration {
	/**
	 * DB dependency.
	 * @var \Asgard\Db\DBInterface
	 */
	protected $db;
	/**
	 * Schema dependency.
	 * @var \Asgard\Db\SchemaInterface
	 */
	protected $schema;

	/**
	 * Set the DB dependency.
	 * @param \Asgard\Db\DBInterface $db
	 */
	public function setDB(\Asgard\Db\DBInterface $db) {
		$this->db = $db;
		return $this;
	}

	/**
	 * Set the Schema dependency.
	 * @param \Asgard\Db\SchemaInterface $schema
	 */
	public function setSchema(\Asgard\Db\SchemaInterface $schema) {
		$this->schema = $schema;
		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function _up() {
		$db = $this->container['db'];

		$db->beginTransaction();
		try {
			parent::_up();
			$db->commit();
		} catch(\Exception $e) {
			$db->rollback();
			throw $e;
		} catch(\Throwable $e) {
			$db->rollback();
			throw $e;
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function _down() {
		$db = $this->container['db'];

		$db->beginTransaction();
		try {
			parent::_down();
			$db->commit();
		} catch(\Exception $e) {
			$db->rollback();
			throw $e;
		} catch(\Throwable $e) {
			$db->rollback();
			throw $e;
		}
	}
}