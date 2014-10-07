<?php
namespace Asgard\Db;

/**
 * Column builder.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class BuildCol {
	/**
	 * Column name.
	 * @var string
	 */
	protected $name;
	/**
	 * Column type.
	 * @var string
	 */
	protected $type;
	/**
	 * Column length.
	 * @var integer
	 */
	protected $length;
	/**
	 * Nullable parameter.
	 * @var boolean
	 */
	protected $nullable = false;
	/**
	 * Autoincrement parameter.
	 * @var boolean
	 */
	protected $autoincrement = false;
	/**
	 * Default value.
	 * @var mixed
	 */
	protected $def;
	/**
	 * BuildTable instance.
	 * @var BuildTable
	 */
	protected $table;

	/**
	 * Constructor.
	 * @param BuildTable $table
	 * @param string     $name
	 * @param string     $type
	 * @param integer    $length
	 */
	public function __construct($table, $name, $type, $length=null) {
		$this->table = $table;
		$this->name = $name;
		$this->type = $type;
		$this->length = $length;
	}

	/**
	 * Set autocrement.
	 * @return BuildCol $this
	 */
	public function autoincrement() {
		$this->autoincrement = true;
		return $this;
	}

	/**
	 * Set nullable.
	 * @return BuildCol $this
	 */
	public function nullable() {
		$this->nullable = true;
		return $this;
	}

	/**
	 * Set default value.
	 * @param  mixed  $def
	 * @return  BuildCol $this
	 */
	public function def($def) {
		$this->def = $def;
		return $this;
	}

	/**
	 * Set column as primary.
	 * @return  BuildCol $this
	 */
	public function primary() {
		$this->table->addPrimary($this->name);
		return $this;
	}

	/**
	 * Set column as unique.
	 * @return  BuildCol $this
	 */
	public function unique() {
		$this->table->addUnique($this->name);
		return $this;
	}

	/**
	 * Set column as index.
	 * @return  BuildCol $this
	 */
	public function index() {
		$this->table->addIndex($this->name);
		return $this;
	}

	/**
	 * Create the SQL query.
	 * @return string
	 */
	public function sql() {
		$sql = '`'.$this->name.'` ';

		if($this->length)
			$sql .= $this->type.'('.$this->length.')';
		else
			$sql .= $this->type;

		if(!$this->nullable)
			$sql .= ' NOT NULL';

		if($this->def)
			$sql .= " DEFAULT '".$this->def."'";

		if($this->autoincrement)
			$sql .= ' AUTO_INCREMENT';

		return $sql;
	}
}

/**
 * Table builder.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class BuildTable {
	/**
	 * Table name.
	 * @var string
	 */
	protected $name;
	/**
	 * Table columns.
	 * @var array
	 */
	protected $cols = [];
	/**
	 * Table primary keys.
	 * @var array
	 */
	protected $primary = [];
	/**
	 * Table index keys.
	 * @var array
	 */
	protected $indexes = [];
	/**
	 * Table unique keys.
	 * @var array
	 */
	protected $uniques = [];

	/**
	 * Constructor.
	 * @param string $name
	 */
	public function __construct($name) {
		$this->name = $name;
	}

	/**
	 * Set table unique keys.
	 * @param  array      $keys
	 * @return BuildTable $this
	 */
	public function unique($keys) {
		$this->uniques = $keys;
		return $this;
	}

	/**
	 * Set table indexes.
	 * @param  array      $keys
	 * @return BuildTable $this
	 */
	public function index($keys) {
		$this->indexes = $keys;
		return $this;
	}

	/**
	 * Set table primary keys.
	 * @param  array      $keys
	 * @return BuildTable $this
	 */
	public function primary($keys) {
		$this->primary = $keys;
		return $this;
	}

	/**
	 * Add a primary key.
	 * @param string $key
	 * @return BuildTable $this
	 */
	public function addPrimary($key) {
		$this->primary[] = $key;
		return $this;
	}

	/**
	 * Add an index.
	 * @param string      $key
	 * @return BuildTable $this
	 */
	public function addIndex($key) {
		$this->indexes[] = $key;
		return $this;
	}

	/**
	 * Add an unique key.
	 * @param string      $key
	 * @return BuildTable $this
	 */
	public function addUnique($key) {
		$this->uniques[] = $key;
		return $this;
	}

	/**
	 * Add a column.
	 * @param string  $colName
	 * @param string  $colType
	 * @param integer $colLength
	 * @return BuildCol
	 */
	public function add($colName, $colType, $colLength=null) {
		$col = new BuildCol($this, $colName, $colType, $colLength);
		$this->cols[] = $col;
		return $col;
	}

	/**
	 * Create the SQL query.
	 * @return string
	 */
	public function sql() {
		$sql = 'CREATE TABLE `'.$this->name.'` (';

		$i = 0;
		foreach($this->cols as $col) {
			if($i++ > 0)
				$sql .= ",\n";
			$sql .= $col->sql();
		}

		if($this->primary) {
			$sql .= ",\n".'PRIMARY KEY (';
			if(is_array($this->primary)) {
				foreach($this->primary as $v)
					$sql .= '`'.$v.'`';
			}
			else
				$sql .= '`'.$this->primary.'`';
			$sql .= ')';
		}

		if($this->indexes) {
			$sql .= ",\n".'INDEX KEY (';
			if(is_array($this->indexes)) {
				foreach($this->indexes as $v)
					$sql .= '`'.$v.'`';
			}
			else
				$sql .= '`'.$this->indexes.'`';
			$sql .= ')';
		}

		if($this->uniques) {
			$sql .= ",\n".'UNIQUE KEY (';
			if(is_array($this->uniques)) {
				foreach($this->uniques as $v)
					$sql .= '`'.$v.'`';
			}
			else
				$sql .= '`'.$this->uniques.'`';
			$sql .= ')';
		}

		$sql .= "\n".') CHARSET=utf8';

		return $sql;
	}
}

/**
 * Table.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Table {
	/**
	 * Database instance.
	 * @var DBInterface
	 */
	protected $db;
	/**
	 * Table name.
	 * @var string
	 */
	protected $name;

	/**
	 * Constructor.
	 * @param DBInterface     $db
	 * @param string $name
	 */
	public function __construct(DBInterface $db, $name) {
		$this->db = $db;
		$this->name = $name;
	}

	/**
	 * Add a column.
	 * @param string $name
	 * @param string $type
	 * @param integer $length
	 * @return Column
	 */
	public function add($name, $type, $length=null) {
		$col = new Column($this->db, $this->name, $name, $type, $length);
		$col->create();
		return $col;
	}

	/**
	 * Access a column.
	 * @param  string $name
	 * @return Column
	 */
	public function col($name) {
		$col = new Column($this->db, $this->name, $name);
		return $col;
	}

	/**
	 * Drop a column.
	 * @param  string $name
	 * @return Column
	 */
	public function drop($name) {
		$col = new Column($this->db, $this->name, $name);
		return $col->drop();
	}

	/**
	 * Set the primary keys.
	 * @param  array $keys
	 * @return Table $this
	 */
	public function primary($keys) {
		try {
			$this->db->query('ALTER TABLE  `'.$this->name.'` DROP PRIMARY KEY');
		} catch(\Asgard\Db\DBException $e) {}

		if(!is_array($keys))
			$keys = [$keys];
		$sql = 'ALTER TABLE  `'.$this->name.'` ADD PRIMARY KEY (';
		foreach($keys as $k=>$v)
			$keys[$k] = '`'.$v.'`';
		$sql .= implode(', ', $keys);
		$sql .= ')';
		$this->db->query($sql);

		return $this;
	}
}

/**
 * Column.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class Column {
	/**
	 * Table name.
	 * @var string
	 */
	protected $table;
	/**
	 * Column name.
	 * @var string
	 */
	protected $name;
	/**
	 * Column type.
	 * @var string
	 */
	protected $type;
	/**
	 * Column length.
	 * @var integer
	 */
	protected $length;
	/**
	 * Database instance.
	 * @var DBInterface
	 */
	protected $db;

	/**
	 * Constructor.
	 * @param DBInterface      $db
	 * @param string  $table
	 * @param string  $name
	 * @param string  $type
	 * @param integer $length
	 */
	public function __construct(DBInterface $db, $table, $name, $type=null, $length=null) {
		$this->db = $db;
		$this->table = $table;
		$this->name = $name;
		$this->type = $type;
		$this->length = $length;
	}

	/**
	 * Drop the column.
	 * @return Column $this
	 */
	public function drop() {
		$sql = 'alter table `'.$this->table.'` drop column `'.$this->name.'`';
		$this->db->query($sql);

		return $this;
	}

	/**
	 * Create the column
	 * @return Column $this
	 */
	public function create() {
		if($this->length)
			$sql = 'ALTER TABLE `'.$this->table.'` ADD `'.$this->name.'` '.$this->type.'('.$this->length.')';
		else
			$sql = 'ALTER TABLE `'.$this->table.'` ADD `'.$this->name.'` '.$this->type;
		$this->db->query($sql);

		return $this;
	}

	/**
	 * Change the column.
	 * @param  array  $params
	 */
	protected function change(array $params) {
		$table = $this->table;
		$oldcol = $this->name;
		$newcol = isset($params['name']) ? $params['name']:$this->name;

		$type = isset($params['type']) ? $params['type']:$this->getType();

		$nullable = isset($params['nullable']) ? $params['nullable']:$this->getNullable();
		if($nullable)
			$nullable = 'NULL';
		else
			$nullable = 'NOT NULL';

		$default = isset($params['default']) ? $params['default']:$this->getDefault();
		if($default)
			$default = "DEFAULT '$default'";
		else
			$default = '';

		$autoincrement = isset($params['autoincrement']) ? $params['autoincrement']:$this->getAutoincrement();
		if($autoincrement)
			$autoincrement = 'auto_increment';
		else
			$autoincrement = '';

		$after = '';
		if(isset($params['after'])) {
			if($params['after'] === false)
				$after = 'FIRST';
			else
				$after = ' AFTER `'.$params['after'].'`';
		}

		$sql = 'ALTER TABLE `'.$table.'` CHANGE `'.$oldcol.'` `'.$newcol.'` '.$type.' '.$default.' '.$nullable.' '.$autoincrement.' '.$after;
		$this->db->query($sql);
	}

	/**
	 * Set the column type.
	 * @param  string  $type
	 * @param  integer $length
	 * @return Column  $this
	 */
	public function type($type, $length=null) {
		$this->type = $type;
		$this->length = $length;
		if($length)
			$type = $this->type.'('.$this->length.')';
		else
			$type = $this->type;

		$this->change(['type'=>$type]);

		return $this;
	}

	/**
	 * Rename the column.
	 * @param  string $name
	 * @return Column $this
	 */
	public function rename($name) {
		$this->change(['name'=>$name]);
		$this->name = $name;
		return $this;
	}

	/**
	 * Set the column nullable.
	 * @return Column $this
	 */
	public function nullable() {
		$this->change(['nullable'=>true]);
		return $this;
	}

	/**
	 * Set the column not nullable.
	 * @return Column $this
	 */
	public function notNullable() {
		$sql = 'UPDATE `'.$this->table.'` set `'.$this->name.'` = 0 where `'.$this->name.'` is null';
		$this->db->query($sql);

		$this->change(['nullable'=>false]);

		return $this;
	}

	/**
	 * Set the column default value.
	 * @param  mixed $val
	 * @return Column $this
	 */
	public function def($val) {
		$this->change(['default'=>$val]);
		return $this;
	}

	/**
	 * Move the column to first position.
	 * @return Column $this
	 */
	public function first() {
		$this->change(['after'=>false]);
		return $this;
	}

	/**
	 * Move the column after another one.
	 * @param  string $column
	 * @return Column $this
	 */
	public function after($column) {
		$this->change(['after'=>$column]);
		return $this;
	}

	/**
	 * Set the column to autoincrement.
	 * @return Column $this
	 */
	public function autoincrement() {
		$this->change(['autoincrement'=>true]);
		return $this;
	}

	/**
	 * Set the column not to autoincrement.
	 * @return Column $this
	 */
	public function notAutoincrement() {
		$this->change(['autoincrement'=>false]);
		return $this;
	}

	/**
	 * Get the column type.
	 * @return string
	 */
	protected function getType() {
		$r = $this->db->query("SELECT *
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = '".$this->db->getConfig()['database']."'
			AND  TABLE_NAME = '$this->table'
			AND COLUMN_NAME = '$this->name'")->first();

		return $r['COLUMN_TYPE'];
	}

	/**
	 * Get the column nullable parameter.
	 * @return boolean
	 */
	protected function getNullable() {
		$r = $this->db->query("SELECT *
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = '".$this->db->getConfig()['database']."'
			AND  TABLE_NAME = '$this->table'
			AND COLUMN_NAME = '$this->name'")->first();

		return $r['IS_NULLABLE'] === 'YES';
	}

	/**
	 * Get the column default value.
	 * @return mixed
	 */
	protected function getDefault() {
		$r = $this->db->query("SELECT *
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = '".$this->db->getConfig()['database']."'
			AND  TABLE_NAME = '$this->table'
			AND COLUMN_NAME = '$this->name'")->first();

		return $r['COLUMN_DEFAULT'];
	}

	/**
	 * Get the column autoincrement parameter.
	 * @return boolean
	 */
	protected function getAutoincrement() {
		$r = $this->db->query("SELECT *
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = '".$this->db->getConfig()['database']."'
			AND  TABLE_NAME = '$this->table'
			AND COLUMN_NAME = '$this->name'")->first();

		return strpos($r['EXTRA'], 'auto_increment') !== false;
	}

	/**
	 * Drop the index.
	 * @return Column $this
	 */
	public function dropIndex() {
		$sql = 'alter table `'.$this->table.'` drop index `'.$this->name.'`';
		try {
			$this->db->query($sql);
		} catch(\Asgard\Db\DBException $e) {}

		return $this;
	}

	/**
	 * Set an index.
	 * @return Column $this
	 */
	public function index() {
		$sql = 'ALTER TABLE `'.$this->table.'` ADD INDEX(`'.$this->name.'`)';
		$this->db->query($sql);

		return $this;
	}

	/**
	 * Set as unique.
	 * @return Column $this
	 */
	public function unique() {
		$sql = 'ALTER TABLE `'.$this->table.'` ADD UNIQUE(`'.$this->name.'`)';
		$this->db->query($sql);

		return $this;
	}

	/**
	 * Set as primary key.
	 * @return Column $this
	 */
	public function primary() {
		$sql = 'ALTER TABLE `'.$this->table.'` ADD PRIMARY KEY (`'.$this->name.'`)';
		$this->db->query($sql);

		return $this;
	}
}

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
	 * Constructor.
	 * @param DBInterface $db
	 */
	public function __construct(DBInterface $db) {
		$this->db = $db;
	}

	/**
	 * {@inheritDoc}
	 */
	public function dropAll() {
		$tables = \Asgard\Common\ArrayUtils::flatten($this->db->query('SHOW TABLES')->all());
		foreach($tables as $table)
			$this->db->query('DROP TABLE '.$table);
	}

	/**
	 * {@inheritDoc}
	 */
	public function create($tableName, $cb) {
		$table = new BuildTable($tableName);
		$cb($table);
		$sql = $table->sql();
		$this->db->query($sql);
	}

	/**
	 * {@inheritDoc}
	 */
	public function emptyTable($tableName) {
		$sql = 'TRUNCATE TABLE  `'.$tableName.'`';
		$this->db->query($sql);
	}

	/**
	 * {@inheritDoc}
	 */
	public function dropColumn($table, $col) {
		$sql = 'alter table `'.$table.'` drop column `'.$col.'`';
		$this->db->query($sql);
	}

	/**
	 * {@inheritDoc}
	 */
	public function drop($table) {
		$sql = 'DROP TABLE IF EXISTS `'.$table.'`';
		$this->db->query($sql);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rename($from, $to) {
		$sql = 'RENAME TABLE `'.$from.'` TO `'.$to.'`';
		$this->db->query($sql);
	}

	/**
	 * {@inheritDoc}
	 */
	public function table($tableName, $cb) {
		$table = new Table($this->db, $tableName);
		$cb($table);
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
}