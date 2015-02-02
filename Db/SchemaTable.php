<?php
namespace Asgard\Db;

/**
 * Doctrine table adapter.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class SchemaTable {
	protected $table;
	protected $renamedColumns = [];

	public function __construct($table) {
		$this->table = $table;
	}

	public function __call($name, $args) {
		return call_user_func_array([$this->table, $name], $args);
	}

	public function getTable() {
		return $this->table;
	}

	public function getRenamedColumns() {
		return $this->renamedColumns;
	}

	public function rename($oldName, $newName) {
		$this->changeColumn($oldName, [
			'name' => $newName
		]);
	}

	public function addColumn($name, $type, $params=[]) {
		if(!isset($params['notnull']))
			$params['notnull'] = false;
		return $this->table->addColumn($name, $type, $params);
	}

	public function changeColumn($name, $params) {
		if(!isset($params['notnull']))
			$params['notnull'] = false;
		if(isset($params['type']))
			$params['type'] = \Doctrine\DBAL\Types\Type::getType($params['type']);
		if(isset($params['name'])) {
			$this->renamedColumns[$name] = $params['name'];
			unset($params['name']);
		}
		return $this->table->changeColumn($name, $params);
	}
}