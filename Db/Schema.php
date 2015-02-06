<?php
namespace Asgard\Db;

/**
 * Schema builder.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Schema implements SchemaInterface {
	/**
	 * Database instance.
	 * @var DBInterface
	 */
	protected $db;

	/**
	 * Doctrine connection instance.
	 * @var \Doctrine\DBAL\Connection
	 */
	protected $conn;

	/**
	 * Constructor.
	 * @param DBInterface $db
	 */
	public function __construct(DBInterface $db) {
		$this->db = $db;
	}

	/**
	 * {@inheritDoc}
	 */
	public function emptyAll() {
		foreach($this->listTables() as $table)
			$this->db->query($this->getPlatform()->getTruncateTableSQL($table->getName()));
	}

	/**
	 * {@inheritDoc}
	 */
	public function dropAll() {
		foreach($this->listTables() as $table)
			$this->db->query($this->getPlatform()->getDropTableSQL($table->getName()));
	}

	public function listTables() {
		return $this->getSchemaManager()->listTables();
	}

	public function hasTable($table) {
		return $this->getSchemaManager()->tablesExist([$table]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function create($tableName, $cb) {
		$schema = new \Doctrine\DBAL\Schema\Schema;
		$table = $schema->createTable($tableName);
		$cb(new SchemaTable($table));

		$platform = $this->getPlatform();
		$queries = $schema->toSql($platform);
		foreach($queries as $query)
			$this->db->query($query);
	}

	/**
	 * {@inheritDoc}
	 */
	public function emptyTable($tableName) {
		$this->db->query($this->getPlatform()->getTruncateTableSQL($tableName));
	}

	/**
	 * {@inheritDoc}
	 */
	public function drop($table) {
		try {
			$this->db->query($this->getPlatform()->getDropTableSQL($table));
		} catch(\Asgard\Db\DBException $e) {}
	}

	/**
	 * {@inheritDoc}
	 */
	public function rename($from, $to) {
		$tableDiff = new \Doctrine\DBAL\Schema\TableDiff($from);
		$tableDiff->newName = $to;
		$queries = $this->getPlatform()->getAlterTableSQL($tableDiff);
		foreach($queries as $query)
			$this->db->query($query);
	}

	/**
	 * {@inheritDoc}
	 */
	public function table($tableName, $cb=null) {
		$platform = $this->getPlatform();
		$sm = $this->getSchemaManager();
		$table = $sm->listTableDetails($tableName);

		if($cb) {
			$clone = new SchemaTable(clone $table);
			$cb($clone);

			$comparator = new \Doctrine\DBAL\Schema\Comparator;
			$tableDiff = $comparator->diffTable($table, $clone->getTable());

			if(!$tableDiff)
				$tableDiff = new \Doctrine\DBAL\Schema\TableDiff($tableName);

			if($renamedColumns = $clone->getRenamedColumns())
				$tableDiff->getRenamedColumns = $renamedColumns;

			$queries = $platform->getAlterTableSQL($tableDiff);
			foreach($queries as $query)
				$this->db->query($query);
		}

		return $table;
	}

	/**
	 * {@inheritDoc}
	 */
	public function renameColumn($table, $old, $new, $type=null) {
		$table = new Table($this->db, $table);
		$col = $table->col($old);
		$col->rename($new);
		if($type)
			$col->type($type);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getConn() {
		return $this->db->getConn();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getPlatform() {
		return $this->getConn()->getDatabasePlatform();
	}
	/**
	 * {@inheritDoc}
	 */
	public function getSchemaManager() {
		return $this->getConn()->getSchemaManager();
	}
}