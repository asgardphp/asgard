<?php
namespace Coxis\DB;

class DAL {
	public $db = null;
	public $table;
	public $selects = array();
	public $where = null;
	public $offset = null;
	public $limit = null;
	public $orderBy = null;
	public $groupBy = null;
	public $joins = array();

	protected $rsc = null;
		
	function __construct($table=null, $db=null) {
		if($db === null)
			$this->db = \DB::inst();
		else
			$this->db = $db;
		$this->table = $table;
	}
	
	public function setTable($table) {
		$this->table = $table;
		
		return $this;
	}

	public function leftjoin($table) {
		$this->joins[] = array('leftjoin', $table);
	}

	public function rightjoin($table) {
		$this->joins[] = array('rightjoin', $table);
	}

	public function innerjoin($table) {
		$this->joins[] = array('innerjoin', $table);
	}

	public function rsc() {
		$query = $this->buildSQL();
		return $this->query($query[0], $query[1]);
	}

	public function next() {
		if($this->rsc === null)
			$this->rsc = $this->rsc();
		return $this->rsc->next();
	}
	
	public function reset() {
		$this->select = null;
		$this->where = null;
		$this->offset = null;
		$this->limit = null;
		$this->orderBy = null;
		$this->groupBy = null;
		$this->joins = array();
		
		return $this;
	}
	
	public function query($sql, $args=array()) {
		return $this->db->query($sql, $args);
	}
	
	/* GETTERS */
	public function first() {
		list($sql, $params) = $this->limit(1)->buildSQL();
		return $this->db->query($sql, $params)->first();
	}
	
	public function get() {
		list($sql, $params) = $this->buildSQL();
		return $this->db->query($sql, $params, $params)->all();
	}
	
	public function paginate($page, $per_page=10) {
		$page = $page ? $page:1;
		$this->offset(($page-1)*$per_page);
		$this->limit($per_page);
		
		return $this;
	}

	/* SETTERS */
	public function select($select) {
		if(!is_array($select))
			$select = array($select);
		$this->selects = $select;
		return $this;
	}
	public function addSelect($select) {
		$this->selects[] = $select;
		return $this;
	}

	public function offset($offset) {
		$this->offset = $offset;
		return $this;
	}
		
	public function limit($limit) {
		$this->limit = $limit;
		return $this;
	}
		
	public function orderBy($orderBy) {
		$this->orderBy = $orderBy;
		return $this;
	}
		
	public function groupBy($groupBy) {
		$this->groupBy = $groupBy;
		return $this;
	}
		
	public function where($conditions) {
		if($this->where === null)
			$this->where = array();
			
		if(!$conditions)
			return $this;
		
		$this->where[] = static::parseConditions($conditions);
		
		return $this;
	}

	/* CONDITIONS PROCESSING */
	protected static function processConditions($params, $condition = 'and', $brackets=false, $table=null) {
		if(sizeof($params) == 0)
			return array('', array());
		
		$string_conditions = '';
		
		if(!is_array($params))
			if($condition == 'and')
				return array($params, array());
			else
				return array(static::replace($condition, $table), array());

		$pdoparams = array();

		foreach($params as $key=>$value) {
			if(!is_array($value)) {
				if(is_int($key))
					$string_conditions[] = $value;
				else {
					$string_conditions[] = static::replace($key);
					$pdoparams[] = $value;
				}
			}
			else {
				if(is_int($key)) {
					$r = static::processConditions($value, 'and', false, $table);
					$string_conditions[] = $r[0];
					$pdoparams[] = $r[1];
				}
				else {
					$r = static::processConditions($value, $key, true, $table);
					$string_conditions[] = $r[0];
					$pdoparams[] = $r[1];
				}
			}
		}

		$result = implode(' '.$condition.' ', $string_conditions);
		
		if($brackets)
			$result = '('.$result.')';
		
		return array($result, Tools::flateArray($pdoparams));
	}
	
	protected static function replace($condition) {
		if(strpos($condition, '?') === false) {
			if(preg_match('/^[a-zA-Z0-9_]+$/', $condition))
				$condition = '`'.$condition.'` = ?';
			else
				$condition = $condition.' = ?';
		}
		
		return $condition;
	}
	
	protected static function parseConditions($conditions) {
		$res = array();

		if(is_array($conditions)) {
			foreach($conditions as $k=>$v)
				if(is_int($k))
					$res[] = static::parseConditions($v);
				else {
					$ar = array();
					$ar[$k] = static::parseConditions($v);
					$res[] = $ar;
				}
			return $res;
		}
		else
			return $conditions;
	}
	
	/* BUILDERS */
	public function buildSelect() {
		if($this->select)
			return $this->select;
		else
			return '*';
	}
	public function buildTables() {
		$tables = array();
		foreach($this->tables as $table=>$alias)
			if($alias)
				$tables[] = '`'.$table.'` '.$alias;
			else
				$tables[] = '`'.$table.'`';
		return implode(', ', $tables);
	}
	public function buildWhere($default=null) {
		if(!$default)
			$default = $this->table;

		$params = array();
		$r = static::processConditions($this->where, 'and', false, $default);
		if($r[0])
			return array(' WHERE '.$r[0], $r[1]);
		else
			return array('', array());
	}
	public function buildGroupby() {
		if($this->groupBy)
			return ' GROUP BY '.$this->groupBy;
	}
	public function buildOrderby($default=null) {
		if(!$this->orderBy)
			return '';

		$orderBy = ' ORDER BY ';
		if(!is_array($this->orderBy))
			$orders = array($this->orderBy);
		else
			$orders = $this->orderBy;
				
		$orderBy .= implode(', ', $orders);
		return $orderBy;
	}

	public function buildJointures() {
		$params = array();
		$jointures = '';
		foreach($this->joins as $jointure) {
			$type = $jointure[0];
			$table = \Coxis\Utils\Tools::get(array_keys($jointure[1]), 0);
			$conditions = \Coxis\Utils\Tools::get(array_values($jointure[1]), 0);
			$res = $this->buildJointure($type, $table, $conditions);
			$jointures .= $res[0];
			$params = array_merge($params, $res[1]);
		}
		return array($jointures, $params);
	}

	public function buildJointure($type, $table, $conditions) {
		$params = array();
		$jointure = '';
		$r = static::processConditions($conditions);
		switch($type) {
			case 'leftjoin':
				$jointure = ' LEFT JOIN ';
				break;
			case 'rightjoin':
				$jointure = ' RIGHT JOIN ';
				break;
			case 'innerjoin':
				$jointure = ' INNER JOIN ';
				break;
		}
		$jointure .= $table.' ON '.$r[0];
		$params = array_merge($params, $r[1]);
		return array($jointure, $params);
	}

	public function buildLimit() {
		if(!$this->limit && !$this->offset)
			return '';

		$limit = ' LIMIT ';
		if($this->offset) {
			$limit .= $this->offset;
			if($this->limit)
				$limit .= ', '.$this->limit;
			else
				$limit .= ', 99999999';
		}
		else
			$limit .= $this->limit;
		return $limit;
	}

	public function buildSQL() {
		$params = array();

		$table = $this->table;
		if(!$this->selects)
			$select = '*';
		elseif(is_array($this->selects))
			$select = implode(', ', $this->selects);
		else
			$select = $this->selects;
		$orderBy = $this->buildOrderBy();
		$limit = $this->buildLimit();
		$groupby = $this->buildGroupby();

		list($jointures, $joinparams) = $this->buildJointures();
		$params = array_merge($params, $joinparams);
		
		list($where, $whereparams) = $this->buildWhere();
		$params = array_merge($params, $whereparams);


		return array('SELECT '.$select.' FROM '.$table.$jointures.$where.$groupby.$orderBy.$limit, $params);
	}

	public function buildDeleteSQL($tables=null) {
		$params = array();

		$table = $this->table;
		if(!$tables)
			$tables = array($table);
		foreach($tables as $k=>$deltable)
			$tables[$k] = '`'.$deltable.'`';

		$limit = $this->buildLimit();

		list($jointures, $joinparams) = $this->buildJointures();
		$params = array_merge($params, $joinparams);
		
		list($where, $whereparams) = $this->buildWhere();
		$params = array_merge($params, $whereparams);

		return array('DELETE '.implode(', ', $tables).' FROM '.$table.$jointures.$where.$limit, $params);
	}

	public function buildInsertSQL($values) {
		if(sizeof($values) == 0)
			throw new Exception('Insert values should not be empty.');

		$params = array();
		$table = $this->table;

		$columns = array();
		foreach($values as $k=>$v)
			$columns[] = $table.'.`'.$k.'`';
		$str = ' ('.implode(', ', $columns).') VALUES ('.implode(', ', array_fill(0, sizeof($values), '?')).')';
		$params = array_merge($params, array_values($values));
		
		return array('INSERT INTO '.$table.$str, $params);
	}

	public function buildUpdateSQL($values) {
		if(sizeof($values) == 0)
			throw new Exception('Update values should not be empty.');

		$params = array();
		$table = $this->table;
		$limit = $this->buildLimit();

		list($jointures, $joinparams) = $this->buildJointures();
		$params = array_merge($params, $joinparams);

		foreach($values as $k=>$v)
			$set[] = $table.'.`'.$k.'`=?';
		$str = ' SET '.implode(', ', $set);
		$params = array_merge($params, array_values($values));
		
		list($where, $whereparams) = $this->buildWhere();
		$params = array_merge($params, $whereparams);

		return array('UPDATE '.$table.$jointures.$str.$where.$limit, $params);
	}
	
	/* FUNCTIONS */
	public function update($values) {
		list($sql, $params) = $this->buildUpdateSQL($values);
		return $this->db->query($sql, $params)->affected();
	}
	
	public function insert($values) {
		list($sql, $params) = $this->buildInsertSQL($values);
		return $this->db->query($sql, $params)->id();
	}
	
	public function delete($tables=null) {
		list($sql, $params) = $this->buildDeleteSQL($tables);
		return $this->db->query($sql, $params)->affected();
	}

	protected function _function($fct, $what=null, $group_by=null) {
		if($what)
			$what = '`'.$what.'`';
		else
			$what = '*';

		if($group_by) {
			$this->select(array($this->table.'.`'.$group_by.'` as groupby', $fct.'('.$what.') as '.$fct))
				->groupBy($this->table.'.`'.$group_by.'`')
				->offset(null)
				->orderBy(null)
				->limit(null);
			$res = array();
			foreach($this->get() as $v)
				$res[$v['groupby']] = $v[$fct];
			return $res;
		}
		else {
			$this->select(array($fct.'('.$what.') as '.$fct))
				->groupBy(null)
				->offset(null)
				->orderBy(null)
				->limit(null);
			return \Coxis\Utils\Tools::get($this->first(), $fct);
		}
	}
	
	public function count($group_by=null) {
		return $this->_function('count', null, $group_by);
	}
	
	public function min($what, $group_by=null) {
		return $this->_function('min', $what, $group_by);
	}
	
	public function max($what, $group_by=null) {
		return $this->_function('max', $what, $group_by);
	}
	
	public function avg($what, $group_by=null) {
		return $this->_function('avg', $what, $group_by);
	}
	
	public function sum($what, $group_by=null) {
		return $this->_function('sum', $what, $group_by);
	}
}
