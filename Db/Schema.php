<?php
namespace Asgard\Db;

class BuildCol {
	protected $name;
	protected $type;
	protected $length;
	protected $nullable = false;
	protected $autoincrement = false;
	protected $def;
	protected $table;
	
	public function __construct($table, $name, $type, $length=null) {
		$this->table = $table;
		$this->name = $name;
		$this->type = $type;
		$this->length = $length;
	}

	public function autoincrement() {
		$this->autoincrement = true;
		return $this;
	}
	
	public function nullable() {
		$this->nullable = true;
		return $this;
	}
	
	public function def($def) {
		$this->def = $def;
		return $this;
	}
	
	public function primary() {
		$this->table->addPrimary($this->name);
	}
	
	public function unique() {
		$this->table->addUnique($this->name);
	}
	
	public function index() {
		$this->table->addIndex($this->name);
	}
	
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

class BuildTable {
	protected $name;
	protected $cols = [];
	protected $primary = [];
	protected $indexes = [];
	protected $uniques = [];
	
	public function __construct($name) {
		$this->name = $name;
	}
	
	public function unique($keys) {
		$this->uniques = $keys;
		return $this;
	}
	
	public function index($keys) {
		$this->indexes = $keys;
		return $this;
	}
	
	public function primary($keys) {
		$this->primary = $keys;
		return $this;
	}
	
	public function addPrimary($key) {
		$this->primary[] = $key;
		return $this;
	}
	
	public function addIndex($key) {
		$this->indexes[] = $key;
		return $this;
	}
	
	public function addUnique($key) {
		$this->uniques[] = $key;
		return $this;
	}
	
	public function add($colName, $colType, $colLength=null) {
		$col = new BuildCol($this, $colName, $colType, $colLength);
		$this->cols[] = $col;
		return $col;
	}
	
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

class Table {
	protected $db;
	protected $name;
	
	public function __construct(DB $db, $name) {
		$this->db = $db;
		$this->name = $name;
	}
	
	public function add($name, $type, $length=null) {
		$col = new Column($this->db, $this->name, $name, $type, $length);
		$col->create();
		return $col;
	}
	
	public function col($name) {
		$col = new Column($this->db, $this->name, $name);
		return $col;
	}
	
	public function drop($name) {
		$col = new Column($this->db, $this->name, $name);
		return $col->drop();
	}
	
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

class Column {
	protected $table;
	protected $name;
	protected $type;
	protected $length;
	protected $db;
	
	public function __construct(DB $db, $table, $name, $type=null, $length=null) {
		$this->db = $db;
		$this->table = $table;
		$this->name = $name;
		$this->type = $type;
		$this->length = $length;
	}
	
	public function drop() {
		$sql = 'alter table `'.$this->table.'` drop column `'.$this->name.'`';
		$this->db->query($sql);
		
		return $this;
	}
	
	public function create() {
		if($this->length)
			$sql = 'ALTER TABLE `'.$this->table.'` ADD `'.$this->name.'` '.$this->type.'('.$this->length.')';
		else
			$sql = 'ALTER TABLE `'.$this->table.'` ADD `'.$this->name.'` '.$this->type;
		$this->db->query($sql);
		
		return $this;
	}
	
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
	
	public function rename($name) {
			
		$this->change(['name'=>$name]);
		
		$this->name = $name;
		
		return $this;
	}
	
	public function nullable() {
		$this->change(['nullable'=>true]);
		
		return $this;
	}
	
	public function notNullable() {
		$sql = 'UPDATE `'.$this->table.'` set `'.$this->name.'` = 0 where `'.$this->name.'` is null';
		$this->db->query($sql);
		
		$this->change(['nullable'=>false]);
		
		return $this;
	}
	
	public function def($val) {
		$this->change(['default'=>$val]);
		
		return $this;
	}
	
	public function first() {
		$this->change(['after'=>false]);
		
		return $this;
	}
	
	public function after($what) {
		$this->change(['after'=>$what]);
		
		return $this;
	}
	
	public function autoincrement() {
		$this->change(['autoincrement'=>true]);
		
		return $this;
	}
	
	public function notAutoincrement() {
		$this->change(['autoincrement'=>false]);
		
		return $this;
	}
	
	protected function getType() {
		$r = $this->db->query("SELECT * 
                 FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = '".$this->db->getConfig()['database']."' 
                 AND  TABLE_NAME = '$this->table'
		 AND COLUMN_NAME = '$this->name'")->first();
		 
		return $r['COLUMN_TYPE'];
	}
	
	protected function getNullable() {
		$r = $this->db->query("SELECT * 
                 FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = '".$this->db->getConfig()['database']."' 
                 AND  TABLE_NAME = '$this->table'
		 AND COLUMN_NAME = '$this->name'")->first();
		 
		return $r['IS_NULLABLE'] === 'YES';
	}
	
	protected function getDefault() {
		$r = $this->db->query("SELECT * 
                 FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = '".$this->db->getConfig()['database']."' 
                 AND  TABLE_NAME = '$this->table'
		 AND COLUMN_NAME = '$this->name'")->first();
		 
		return $r['COLUMN_DEFAULT'];
	}
	
	protected function getAutoincrement() {
		$r = $this->db->query("SELECT * 
                 FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = '".$this->db->getConfig()['database']."' 
                 AND  TABLE_NAME = '$this->table'
		 AND COLUMN_NAME = '$this->name'")->first();
		 
		return strpos($r['EXTRA'], 'auto_increment') !== false;
	}
	
	public function dropIndex() {
		$sql = 'alter table `'.$this->table.'` drop index `'.$this->name.'`';
		try {
		$this->db->query($sql);
		} catch(\Asgard\Db\DBException $e) {}
		
		return $this;
	}
	
	public function index() {
		$sql = 'ALTER TABLE `'.$this->table.'` ADD INDEX(`'.$this->name.'`)';
		$this->db->query($sql);
		
		return $this;
	}
	
	public function unique() {
		$sql = 'ALTER TABLE `'.$this->table.'` ADD UNIQUE(`'.$this->name.'`)';
		$this->db->query($sql);
		
		return $this;
	}
	
	public function primary() {
		$sql = 'ALTER TABLE `'.$this->table.'` ADD PRIMARY(`'.$this->name.'`)';
		$this->db->query($sql);
		
		return $this;
	}
}

class Schema {
	protected $db;

	public function __construct(DB $db) {
		$this->db = $db;
	}

	public function dropAll() {
		$tables = \Asgard\Common\ArrayUtils::flateArray($this->db->query('SHOW TABLES')->all());
		foreach($tables as $table)
			$this->db->query('DROP TABLE '.$table);
	}

	public function create($tableName, $cb) {
		$table = new BuildTable($tableName);
		$cb($table);
		$sql = $table->sql();
		$this->db->query($sql);
	}
	
	public function emptyTable($tableName) {
		$sql = 'TRUNCATE TABLE  `'.$tableName.'`';
		$this->db->query($sql);
	}
	
	public function dropColumn($table, $col) {
		$sql = 'alter table `'.$table.'` drop column `'.$col.'`';
		$this->db->query($sql);
	}
	
	public function drop($table) {
		$sql = 'DROP TABLE `'.$table.'`';
		$this->db->query($sql);
	}
	
	public function rename($from, $to) {
		$sql = 'RENAME TABLE `'.$from.'` TO `'.$to.'`';
		$this->db->query($sql);
	}
	
	public function table($tableName, $cb) {
		$table = new Table($this->db, $tableName);
		$cb($table);
	}
	
	public function renameColumn($table, $old, $new, $type=null) {
		$table = new Table($this->db, $table);
		$col = $table->col($old);
		$col->rename($new);
		if($type)
			$col->type($type);
	}
}