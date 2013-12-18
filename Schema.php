<?php
namespace Coxis\DB;

class BuildCol {
	protected $name;
	protected $type;
	protected $length;
	protected $nullable = false;
	protected $autoincrement = false;
	protected $def;
	
	function __construct($table, $name, $type, $length=null) {
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
		$this->$def = $def;
		return $this;
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
	
	public function primary() {
		$this->table->addPrimary($this->name);
	}
	
	public function unique() {
		$this->table->addUnique($this->name);
	}
	
	public function index() {
		$this->table->addIndex($this->name);
	}
}

class BuildTable {
	protected $name;
	protected $cols = array();
	protected $primary = array();
	protected $indexes = array();
	protected $uniques = array();
	
	function __construct($name) {
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
			if(is_array($this->primary))
				foreach($this->primary as $v)
					$sql .= '`'.$v.'`';
			else
				$sql .= '`'.$this->primary.'`';
			$sql .= ')';
		}
		
		if($this->indexes) {
			$sql .= ",\n".'INDEX KEY (';
			if(is_array($this->indexes))
				foreach($this->indexes as $v)
					$sql .= '`'.$v.'`';
			else
				$sql .= '`'.$this->indexes.'`';
			$sql .= ')';
		}
		
		if($this->uniques) {
			$sql .= ",\n".'UNIQUE KEY (';
			if(is_array($this->uniques))
				foreach($this->uniques as $v)
					$sql .= '`'.$v.'`';
			else
				$sql .= '`'.$this->uniques.'`';
			$sql .= ')';
		}
		
		$sql .= "\n".') CHARSET=utf8';
		
		return $sql;
	}
}

class Table {
	protected $name;
	
	function __construct($name) {
		$this->name = $name;
	}
	
	public function add($name, $type, $length=null) {
		$col = new Column($this->name, $name, $type, $length);
		$col->create();
		return $col;
	}
	
	public function col($name) {
		$col = new Column($this->name, $name);
		return $col;
	}
	
	public function drop($name) {
		$col = new Column($this->name, $name);
		return $col->drop();
	}
	
	public function primary($keys) {
		try {
			\DB::query('ALTER TABLE  `'.$this->name.'` DROP PRIMARY KEY');
		} catch(\Coxis\DB\DBException $e) {}
	
		if(!is_array($keys))
			$keys = array($keys);
		$sql = 'ALTER TABLE  `'.$this->name.'` ADD PRIMARY KEY (';
		foreach($keys as $k=>$v)
			$keys[$k] = '`'.$v.'`';
		$sql .= implode(', ', $keys);
		$sql .= ')';
		\DB::query($sql);
		
		return $this;
	}
}

class Column {
	protected $table;
	protected $name;
	protected $type;
	protected $length;
	
	function __construct($table, $name, $type=null, $length=null) {
		$this->table = $table;
		$this->name = $name;
		$this->type = $type;
		$this->length = $length;
	}
	
	public function drop() {
		$sql = 'alter table `'.$this->table.'` drop column `'.$this->name.'`';
		\DB::query($sql);
		
		return $this;
	}
	
	public function create() {
		if($this->length)
			$sql = 'ALTER TABLE `'.$this->table.'` ADD `'.$this->name.'` '.$this->type.'('.$type->length.')';
		else
			$sql = 'ALTER TABLE `'.$this->table.'` ADD `'.$this->name.'` '.$this->type;
		\DB::query($sql);
		
		return $this;
	}
	
	#name
	#type
	#nullable
	#default
	#autoincrement
	
	protected function change($params) {
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
		
		
		
		$sql = 'ALTER TABLE `'.$table.'` CHANGE `'.$oldcol.'` `'.$newcol.'` '.$type.' '.$default.' '.$nullable.' '.$autoincrement;
		//~ d($sql);
		\DB::query($sql);
		//~ ALTER TABLE `test` CHANGE `title2` `title3` varchar(100) DEFAULT 'bob' NOT NULL auto_increment
		//~ ALTER TABLE `test` CHANGE `title2` `title3` varchar(100) NOT NULL auto_increment DEFAULT 'bob'
	}
	
	public function type($type, $length=null) {
		$this->type = $type;
		$this->length = $length;
		if($length)
			$type = $this->type.'('.$this->length.')';
		else
			$type = $this->type;
			
		$this->change(array('type'=>$type));
	
		//~ Schema::renameColumn($this->table, $this->name, $this->name, $type);
		
		return $this;
	}
	
	public function rename($name) {
			
		$this->change(array('name'=>$name));
		
		//~ Schema::renameColumn($this->table, $this->name, $name);
		$this->name = $name;
		
		return $this;
	}
	
	public function nullable() {
		$this->change(array('nullable'=>true));
		
		return $this;
	}
	
	public function notNullable() {
		$sql = 'UPDATE `'.$this->table.'` set `'.$this->name.'` = 0 where `'.$this->name.'` is null';
		\DB::query($sql);
		
		$this->change(array('nullable'=>false));
		
		return $this;
	}
	
	public function def($val) {
		$this->change(array('default'=>$val));
		
		return $this;
	}
	
	public function autoincrement() {
		$this->change(array('autoincrement'=>true));
		
		return $this;
	}
	
	public function notAutoincrement() {
		$this->change(array('autoincrement'=>false));
		
		return $this;
	}
	
	protected function getType() {
		$r = \DB::query("SELECT * 
                 FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = '".\Config::get('database/database')."' 
                 AND  TABLE_NAME = '$this->table'
		 AND COLUMN_NAME = '$this->name'")->first();
		 
		return $r['COLUMN_TYPE'];
	}
	
	protected function getNullable() {
		$r = \DB::query("SELECT * 
                 FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = '".\Config::get('database/database')."' 
                 AND  TABLE_NAME = '$this->table'
		 AND COLUMN_NAME = '$this->name'")->first();
		 
		return $r['IS_NULLABLE'] === 'YES';
	}
	
	protected function getDefault() {
		$r = \DB::query("SELECT * 
                 FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = '".\Config::get('database/database')."' 
                 AND  TABLE_NAME = '$this->table'
		 AND COLUMN_NAME = '$this->name'")->first();
		 
		return $r['COLUMN_DEFAULT'];
	}
	
	protected function getAutoincrement() {
		$r = \DB::query("SELECT * 
                 FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = '".\Config::get('database/database')."' 
                 AND  TABLE_NAME = '$this->table'
		 AND COLUMN_NAME = '$this->name'")->first();
		 
		return strpos($r['EXTRA'], 'auto_increment') !== false;
	}
	
	public function dropIndex() {
		$sql = 'alter table `'.$this->table.'` drop index `'.$this->name.'`';
		try {
		\DB::query($sql);
		} catch(\Coxis\DB\DBException $e) {}
		
		return $this;
	}
	
	public function index() {
		$sql = 'ALTER TABLE `'.$this->table.'` ADD INDEX(`'.$this->name.'`)';
		\DB::query($sql);
		
		return $this;
	}
	
	public function unique() {
		$sql = 'ALTER TABLE `'.$this->table.'` ADD UNIQUE(`'.$this->name.'`)';
		\DB::query($sql);
		
		return $this;
	}
	
	public function primary() {
		$sql = 'ALTER TABLE `'.$this->table.'` ADD PRIMARY(`'.$this->name.'`)';
		\DB::query($sql);
		
		return $this;
	}
}

class Schema {
	public static function dropAll() {
		$tables = Tools::flateArray(\DB::query('SHOW TABLES')->all());
		foreach($tables as $table)
			\DB::query('DROP TABLE '.$table);
	}

	public static function create($tableName, $cb) {
		$table = new BuildTable($tableName);
		$cb($table);
		$sql = $table->sql();
		\DB::query($sql);
	}
	
	public static function emptyTable($tableName) {
		$sql = 'TRUNCATE TABLE  `'.$tableName.'`';
		\DB::query($sql);
	}
	
	public static function dropColumn($table, $col) {
		$sql = 'alter table `'.$table.'` drop column `'.$col.'`';
		\DB::query($sql);
	}
	
	public static function drop($table) {
		$sql = 'DROP TABLE `'.$table.'`';
		\DB::query($sql);
	}
	
	public static function rename($from, $to) {
		$sql = 'RENAME TABLE `'.$from.'` TO `'.$to.'`';
		\DB::query($sql);
	}
	
	public static function table($tableName, $cb) {
		$table = new Table($tableName);
		$cb($table);
	}
	
	public static function renameColumn($table, $old, $new, $type=null) {
		$table = new Table($table);
		$col = $table->col($old);
		$col->rename($new);
		if($type)
			$col->type($type);
	}
	
	public static function getType($table, $column) {
		$table = new BuildTable($tableName);
		$col = $table->col($old);
		
		return $col->getType();
	}
}