<?php
namespace Asgard\Db;

/**
 * Database Abstraction Layer.
 */
class DAL {
	/**
	 * Database instance.
	 * @var DB
	 */
	public $db;
	/**
	 * Paginator factory.
	 * @var \Asgard\Container\Factory
	 */
	protected $paginatorFactory;
	/**
	 * Tables to access.
	 * @var array
	 */
	public $tables  = [];
	/**
	 * Columns to access.
	 * @var array
	 */
	public $columns = [];
	/**
	 * Where conditions.
	 * @var array
	 */
	public $where   = [];
	/**
	 * Jointures.
	 * @var array
	 */
	public $joins   = [];
	/**
	 * Parameters.
	 * @var array
	 */
	public $params  = [];
	/**
	 * Offset.
	 * @var integer
	 */
	public $offset;
	/**
	 * Limit.
	 * @var integer
	 */
	public $limit;
	/**
	 * Order by.
	 * @var string
	 */
	public $orderBy;
	/**
	 * Group by.
	 * @var string
	 */
	public $groupBy;
	/**
	 * Into table.
	 * @var string
	 */
	public $into;
	/**
	 * Page number.
	 * @var integer
	 */
	public $page;
	/**
	 * Number of rows per page.
	 * @var integer
	 */
	public $per_page;
	/**
	 * Query instance.
	 * @var Query
	 */
	protected $query;
	/**
	 * 
	 */

	/**
	 * Constructor.
	 * @param DB     $db
	 * @param array  $tables
	 */
	public function __construct(DB $db, $tables=null) {
		$this->db = $db;
		$this->addFrom($tables);
	}

    /**
     * Return the DAL parameters.
     * @return array
     */
    public function getParameters() {
		return $this->params;
	}
	
	/**
	 * Set FROM tables.
	 * @param  string $tables  Separated by ,
	 * @return DAL    $this
	 */
	public function from($tables) {
		$this->tables = [];
		return $this->addFrom($tables);
	}
	
	/**
	 * INTO table.
	 * @param  string $table
	 * @return DAL    $this
	 */
	public function into($table) {
		$this->into = $table;
		return $this;
	}

	/**
	 * Add FROM tables.
	 * @param string $tables  Separated by ,
	 * @return DAL  $this
	 */
	public function addFrom($tables) {
		if(!$tables)
			return $this;

		$tables = explode(',', $tables);
		foreach($tables as $tablestr) {
			$tablestr = trim($tablestr);

			preg_match('/(.*?) ([a-z_][a-z0-9_]*)?$/i', $tablestr, $matches);
			if(isset($matches[2])) {
				$alias = $matches[2];
				$table = $matches[1];
			}
			else
				$alias = $table = $tablestr;

			if(isset($this->tables[$alias]))
				throw new \Exception('Table alias '.$alias.' is already used.');
			$this->tables[$alias] = $table;
		}
		
		return $this;
	}

	/**
	 * Remove a FROM table.
	 * @param  string $what
	 * @return DALm   $this
	 */
	public function removeFrom($what) {
		foreach($this->tables as $alias=>$table) {
			if($alias === $what) {
				unset($this->tables[$alias]);
				break;
			}
		}

		return $this;
	}

	/**
	 * Create a jointure.
	 * @param  string $type
	 * @param  string $table
	 * @param  array  $conditions
	 * @return DAL    $this
	 */
	protected function join($type, $table, $conditions=null) {
		if(is_array($table)) {
			foreach($table as $_table=>$_conditions)
				$this->leftjoin($_table, $_conditions);
			return $this;
		}
		$table_alias = explode(' ', $table);
		$table = $table_alias[0];
		if(isset($table_alias[1]))
			$alias = $table_alias[1];
		else
			$alias = $table;
		$this->joins[$alias] = [$type, $table, $conditions];
		return $this;
	}

	/**
	 * Add a LEFT jointure.
	 * @param  string $table
	 * @param  array  $conditions
	 * @return DAL    $this
	 */
	public function leftjoin($table, $conditions=null) {
		return $this->join('leftjoin', $table, $conditions);
	}

	/**
	 * Add a RIGHT jointure.
	 * @param  string $table
	 * @param  array  $conditions
	 * @return DAL    $this
	 */
	public function rightjoin($table, $conditions=null) {
		return $this->join('rightjoin', $table, $conditions);
	}

	/**
	 * Add an INNER jointure.
	 * @param  string $table
	 * @param  array  $conditions
	 * @return DAL    $this
	 */
	public function innerjoin($table, $conditions=null) {
		return $this->join('innerjoin', $table, $conditions);
	}

	/**
	 * Return the next row.
	 * @return array
	 */
	public function next() {
		if($this->query === null)
			$this->query = $this->query();
		return $this->query->next();
	}
	
	/**
	 * Reset all parameters.
	 * @return DAL  $this
	 */
	public function reset() {
		$this->tables = [];
		$this->columns = [];
		$this->where = [];
		$this->offset = null;
		$this->limit = null;
		$this->orderBy = null;
		$this->groupBy = null;
		$this->joins = [];
		$this->params = [];
		
		return $this;
	}
	
	/**
	 * Execute a query.
	 * @param  string $sql
	 * @param  array  $params
	 * @return Query
	 */
	public function query($sql=null, array $params=[]) {
		if($sql === null) {
			$sql = $this->buildSQL();
			$params = $this->getParameters();
			return $this->query($sql, $params);
		}

		return $this->db->query($sql, $params);
	}
	
	/**
	 * Return the first row only.
	 * @return array
	 */
	public function first() {
		return $this->query()->first();
	}
	
	/**
	 * Return selected rows.
	 * @return array
	 */
	public function get() {
		return $this->query()->all();
	}
	
	/**
	 * Paginate the results.
	 * @param  integer  $page
	 * @param  integer  $per_page
	 * @return DAL    $this
	 */
	public function paginate($page, $per_page=10) {
		$this->page = $page = $page ? $page:1;
		$this->per_page = $per_page;
		$this->offset(($page-1)*$per_page);
		$this->limit($per_page);
		
		return $this;
	}

	/**
	 * Set the paginator factory.
	 * @param \Asgard\Container\Factory $paginatorFactory
	 * @return DAL    $this
	 */
	public function setPaginatorFactory($paginatorFactory) {
		$this->paginatorFactory = $paginatorFactory;
		return $this;
	}

	/**
	 * Get a paginator instance.
	 * @return \Asgard\Common\Paginator
	 */
	public function getPaginator() {
		if($this->page === null || $this->per_page === null)
			return;
		if(!$this->paginatorFactory)
			return new \Asgard\Common\Paginator($this->count(), $this->page, $this->per_page);
		return $this->paginatorFactory->create([$this->count(), $this->page, $this->per_page]);
	}

	/**
	 * Set SELECT columns.
	 * @param  string|array $columns
	 * @return DAL    $this
	 */
	public function select($columns) {
		$this->columns = [];
		return $this->addSelect($columns);
	}

	/**
	 * Add SELECT columns.
	 * @param string|array $columns
	 * @return DAL    $this
	 */
	public function addSelect($columns) {
		if(is_array($columns))
			return $this->_addSelect($columns);

		$columns = explode(',', $columns);
		foreach($columns as $columnstr) {
			$columnstr = trim($columnstr);

			preg_match('/(.*?) ([a-z_][a-z0-9_]*)?$/i', $columnstr, $matches);
			if(isset($matches[2])) {
				$alias = $matches[2];
				$column = $matches[1];
			}
			else
				$alias = $column = $columnstr;

			if(isset($this->columns[$alias]))
				throw new \Exception('Column alias '.$alias.' is not already used.');
			$this->columns[$alias] = $column;
		}
		
		return $this;
	}

	/**
	 * Add array of SELECT columns.
	 * @param array $columns
	 */
	protected function _addSelect(array $columns) {
		if(array_values($columns) === $columns) {
			foreach($columns as $k=>$v) {
				unset($columns[$k]);
				$columns[$v] = $v;
			}
		}
		$this->columns = array_merge($this->columns, $columns);
		return $this;
	}

	/**
	 * Remove a SELECT column.
	 * @param  string $what
	 * @return DAL    $this
	 */
	public function removeSelect($what) {
		foreach($this->columns as $alias=>$column) {
			if($alias === $what) {
				unset($this->columns[$alias]);
				break;
			}
		}
		return $this;
	}

	/**
	 * Set offset.
	 * @param  integer $offset
	 * @return DAL    $this
	 */
	public function offset($offset) {
		$this->offset = $offset;
		return $this;
	}
	
	/**
	 * Set limit.
	 * @param  integer $limit
	 * @return DAL    $this
	 */
	public function limit($limit) {
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Set order by.
	 * @param  string $orderBy
	 * @return DAL    $this
	 */
	public function orderBy($orderBy) {
		$this->orderBy = $orderBy;
		return $this;
	}
	
	/**
	 * Set group by.
	 * @param  string $groupBy
	 * @return DAL    $this
	 */
	public function groupBy($groupBy) {
		$this->groupBy = $groupBy;
		return $this;
	}
	
	/**
	 * Add WHERE conditions.
	 * @param  array|string $conditions
	 * @param  mixed $values
	 * @return DAL    $this
	 */
	public function where($conditions, $values=null) {
		if($values !== null)
			$this->where[$conditions] = $values;
		else		
			$this->where[] = $conditions;
		
		return $this;
	}

	/**
	 * Format the conditions.
	 * @param  array  $params
	 * @param  string  $condition
	 * @param  boolean $brackets
	 * @param  string  $table
	 * @return array   first element is the SQL, second is the parameters.
	 */
	protected function processConditions($params, $condition = 'and', $brackets=false, $table=null) {
		if(count($params) == 0)
			return ['', []];
		
		$string_conditions = [];
		
		if(!is_array($params)) {
			if($condition == 'and')
				return [$this->replace($params), []];
			else
				return [$this->replace($condition), []];
		}

		$pdoparams = [];

		foreach($params as $key=>$value) {
			if(!is_array($value)) {
				if(is_int($key))
					$string_conditions[] = $this->replace($value);
				else {
					$res = $this->replace($key);
					if(static::isIdentifier($key))
						$res .= '=?';
					$string_conditions[] = $res;
					$pdoparams[] = $value;
				}
			}
			else {
				if(is_int($key))
					$key = 'and';
				$r = $this->processConditions($value, $key, $brackets || count($params) > 1, $table);
				$string_conditions[] = $r[0];
				$pdoparams[] = $r[1];
			}
		}

		$result = implode(' '.strtoupper($condition).' ', $string_conditions);
		
		if($brackets && count($params) > 1)
			$result = '('.$result.')';
		
		return [$result, \Asgard\Common\ArrayUtils::flateArray($pdoparams)];
	}

	/**
	 * Remove a jointure.
	 * @param  string $alias
	 * @return DAL    $this
	 */
	public function removeJointure($alias) {
		unset($this->joins[$alias]);
		return $this;
	}
	
	/**
	 * Format identifiers.
	 * @param  string $condition
	 * @return string
	 */
	protected function replace($condition) {
		$condition = preg_replace_callback('/[a-z_][a-z0-9._]*(?![^\(]*\))/', function($matches) {
			if(strpos($matches[0], '.')===false && count($this->joins) > 0 && count($this->tables)===1)
				$matches[0] = array_keys($this->tables)[0].'.'.$matches[0];

			return $this->identifierQuotes($matches[0]);
		}, $condition);

		return $condition;
	}

	/**
	 * Check if string is an identifier.
	 * @param  string  $str
	 * @return boolean
	 */
	protected static function isIdentifier($str) {
		return preg_match('/^[a-z_][a-z0-9._]*$/', $str);
	}

	/**
	 * Quote idenfitiers.
	 * @param  string $str
	 * @return string
	 */
	protected function identifierQuotes($str) {
		return preg_replace_callback('/[a-z_][a-z0-9._]*/', function($matches) {
			$res = [];
			foreach(explode('.', $matches[0]) as $substr)
				$res[] = '`'.$substr.'`';
			return implode('.', $res);
		}, $str);
	}
	
	/**
	 * Build the list of columns.
	 * @return string
	 */
	protected function buildColumns() {
		$select = [];
		if(!$this->columns)
			return '*';
		else {
			foreach($this->columns as $alias=>$table) {
				if($alias !== $table) {
					if($this->isIdentifier($table))
						$select[] = $this->identifierQuotes($table).' AS '.$this->identifierQuotes($alias);
					else
						$select[] = $table.' AS '.$this->identifierQuotes($alias);
				}
				else {
					if($this->isIdentifier($table))
						$select[] = $this->identifierQuotes($table);
					else
						$select[] = $table;
				}
			}
		}
		return implode(', ', $select);
	}

	/**
	 * Return the default table.
	 * @return string
	 */
	protected function getDefaultTable() {
		if(count($this->tables) === 1)
			return array_keys($this->tables)[0];
		else
			return null;
	}

	/**
	 * Build the WHERE conditions.
	 * @param  null|string $default
	 * @return array  1st element is the SQL, 2nd is the parameters.
	 */
	protected function buildWhere($default=null) {
		$r = $this->processConditions($this->where, 'and', false, $default!==null ? $default:$this->getDefaultTable());
		if($r[0])
			return [' WHERE '.$r[0], $r[1]];
		else
			return ['', []];
	}

	/**
	 * Build GROUP BY.
	 * @return string
	 */
	protected function buildGroupby() {
		if(!$this->groupBy)
			return;

		$res = [];

		foreach(explode(',', $this->groupBy) as $column) {
			if($this->isIdentifier(trim($column)))
				$res[] = $this->replace(trim($column));
			else
				$res[] = trim($column);
		}

		return ' GROUP BY '.implode(', ', $res);
	}

	/**
	 * Build ORDER By.
	 * @return string
	 */
	protected function buildOrderby() {
		if(!$this->orderBy)
			return;

		$res = [];

		foreach(explode(',', $this->orderBy) as $orderbystr) {
			$orderbystr = trim($orderbystr);

			preg_match('/(.*?) (ASC|DESC)?$/i', $orderbystr, $matches);

			if(isset($matches[2])) {
				$direction = $matches[2];
				$column = $matches[1];

				if($this->isIdentifier($column))
					$res[] = $this->replace($column).' '.$direction;
				else
					$res[] = $column.' '.$direction;
			}
			else {
				$column = $orderbystr;

				if($this->isIdentifier($column))
					$res[] = $this->replace($column);
				else
					$res[] = $column;
			}
		}

		return ' ORDER BY '.implode(', ', $res);
	}

	/**
	 * Build jointures.
	 * @return string
	 */
	protected function buildJointures() {
		$params = [];
		$jointures = '';
		foreach($this->joins as $alias=>$jointure) {
			$type = $jointure[0];
			$table = $jointure[1];
			$conditions = $jointure[2];
			$alias = $alias !== $table ? $alias:null;
			$res = $this->buildJointure($type, $table, $conditions, $alias);
			$jointures .= $res[0];
			$params = array_merge($params, $res[1]);
		}
		return [$jointures, $params];
	}

	/**
	 * Build a jointure.
	 * @param  string $type
	 * @param  string $table
	 * @param  array  $conditions
	 * @param  string $alias
	 * @return string
	 */
	protected function buildJointure($type, $table, $conditions, $alias=null) {
		$params = [];
		$jointure = '';
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

		if($alias !== null)
			$table = $table.' '.$alias;
		$table = preg_replace_callback('/(^[a-z_][a-z0-9._]*)| ([a-z_][a-z0-9._]*$)/', function($matches) {
				return $this->identifierQuotes($matches[0]);
		}, $table);

		$jointure .= $table;
		if($conditions) {
			$r = $this->processConditions($conditions);
			$jointure .= ' ON '.$r[0];
			$params = array_merge($params, $r[1]);
		}
		return [$jointure, $params];
	}

	/**
	 * Build LIMIT
	 * @return string
	 */
	protected function buildLimit() {
		if(!$this->limit && !$this->offset)
			return '';

		$limit = ' LIMIT ';
		if($this->offset) {
			$limit .= $this->offset;
			if($this->limit)
				$limit .= ', '.$this->limit;
			else
				$limit .= ', 18446744073709551615';
		}
		else
			$limit .= $this->limit;
		return $limit;
	}

	/**
	 * Build the lit of tables.
	 * @param  boolean $with_alias  Add aliases or not.
	 * @return string
	 */
	public function buildTables($with_alias=true) {
		$tables = [];
		if(!$this->tables)
			throw new \Exception('Must set tables with method from($tables) before running the query.');
		foreach($this->tables as $alias=>$table) {
			if($alias !== $table && $with_alias)
				$tables[] = '`'.$table.'` `'.$alias.'`';
			else
				$tables[] = '`'.$table.'`';
		}
		return implode(', ', $tables);
	}

	/**
	 * Build a SELECT SQL query.
	 * @return string
	 */
	public function buildSQL() {
		$params = [];

		$tables = $this->buildTables();
		$columns = $this->buildColumns();
		$orderBy = $this->buildOrderBy();
		$limit = $this->buildLimit();
		$groupby = $this->buildGroupby();

		list($jointures, $joinparams) = $this->buildJointures();
		$params = array_merge($params, $joinparams);
		
		list($where, $whereparams) = $this->buildWhere();
		$params = array_merge($params, $whereparams);

		$this->params = $params;
		return 'SELECT '.$columns.' FROM '.$tables.$jointures.$where.$groupby.$orderBy.$limit;
	}

	/**
	 * Build an UPDATE SQL query.
	 * @param  array  $values
	 * @return string
	 */
	public function buildUpdateSQL(array $values) {
		if(count($values) == 0)
			throw new \Exception('Update values should not be empty.');
		$params = [];

		$tables = $this->buildTables();
		$orderBy = $this->buildOrderBy();
		$limit = $this->buildLimit();

		list($jointures, $joinparams) = $this->buildJointures();
		$params = array_merge($params, $joinparams);

		$set = [];
		foreach($values as $k=>$v)
			$set[] = $this->replace($k).'=?';
		$str = ' SET '.implode(', ', $set);
		$params = array_merge($params, array_values($values));
		
		list($where, $whereparams) = $this->buildWhere();
		$params = array_merge($params, $whereparams);
		

		$this->params = $params;
		return 'UPDATE '.$tables.$jointures.$str.$where.$orderBy.$limit;
	}

	/**
	 * Build a DELETE SQL query.
	 * @param  array $del_tables
	 * @return string
	 */
	public function buildDeleteSQL(array $del_tables=[]) {
		$params = [];

		$tables = $this->buildTables(count($del_tables) > 0);
		$orderBy = $this->buildOrderBy();
		$limit = $this->buildLimit();

		list($jointures, $joinparams) = $this->buildJointures();
		$params = array_merge($params, $joinparams);
		
		list($where, $whereparams) = $this->buildWhere();
		$params = array_merge($params, $whereparams);

		$this->params = $params;
		if($del_tables) {
			foreach($del_tables as $k=>$v)
				$del_tables[$k] = '`'.$v.'`';
			$del_tables = implode(', ', $del_tables);
			return 'DELETE '.$del_tables.' FROM '.$tables.$jointures.$where.$orderBy.$limit;
		}
		else
			return 'DELETE FROM '.$tables.$jointures.$where.$orderBy.$limit;
	}

	/**
	 * Build an INSERT SQL query.
	 * @param  array  $values
	 * @return string
	 */
	public function buildInsertSQL(array $values) {
		if(count($values) == 0)
			throw new \Exception('Insert values should not be empty.');
		if($this->into === null && count($this->tables) !== 1)
			throw new \Exception('The into table is not defined.');
		if($this->into !== null)
			$into = $this->into;
		else
			$into = array_keys($this->tables)[0];

		$params = [];
		$into = $this->identifierQuotes($into);

		$cols = [];
		foreach($values as $k=>$v)
			$cols[] = $this->replace($k);
		$str = ' ('.implode(', ', $cols).') VALUES ('.implode(', ', array_fill(0, count($values), '?')).')';
		$params = array_merge($params, array_values($values));
		
		$this->params = $params;
		return 'INSERT INTO '.$into.$str;
	}
	
	/**
	 * Update rows.
	 * @param  array  $values
	 * @return Query
	 */
	public function update(array $values) {
		$sql = $this->buildUpdateSQL($values);
		$params = $this->getParameters();
		return $this->db->query($sql, $params)->affected();
	}
	
	/**
	 * Insert rows.
	 * @param  array  $values
	 * @return Query
	 */
	public function insert(array $values) {
		$sql = $this->buildInsertSQL($values);
		$params = $this->getParameters();
		$this->db->query($sql, $params);
		return $this->db->id();
	}
	
	/**
	 * Delete rows.
	 * @param  array $tables
	 * @return Query
	 */
	public function delete(array $tables=[]) {
		$sql = $this->buildDeleteSQL($tables);
		$params = $this->getParameters();
		return $this->db->query($sql, $params)->affected();
	}

	/**
	 * Execute a math function.
	 * @param  string      $fct
	 * @param  null|string $what
	 * @param  null|string $group_by
	 * @return string
	 */
	protected function _function($fct, $what=null, $group_by=null) {
		if($what === null)
			$what = '*';

		$dal = clone $this;
		if($group_by) {
			$dal->select($group_by.' groupby, '.strtoupper($fct).'('.$what.') '.$fct)
				->groupBy($group_by)
				->offset(null)
				->orderBy(null)
				->limit(null);
			$res = [];
			foreach($dal->get() as $v)
				$res[$v['groupby']] = $v[$fct];
			return $res;
		}
		else {
			$dal->select($fct.'('.$what.') '.$fct)
				->groupBy(null)
				->offset(null)
				->orderBy(null)
				->limit(null);
			return \Asgard\Common\ArrayUtils::array_get($dal->first(), $fct);
		}
	}
	
	/**
	 * Count number of rows.
	 * @param  null|string $group_by
	 * @return string
	 */
	public function count($group_by=null) {
		return $this->_function('count', null, $group_by);
	}
	
	/**
	 * Return the minimum value.
	 * @param  string      $what
	 * @param  null|string $group_by
	 * @return string
	 */
	public function min($what, $group_by=null) {
		return $this->_function('min', $what, $group_by);
	}
	
	/**
	 * Return the maximum value.
	 * @param  string      $what
	 * @param  null|string $group_by
	 * @return string
	 */
	public function max($what, $group_by=null) {
		return $this->_function('max', $what, $group_by);
	}
	
	/**
	 * Return the average value.
	 * @param  string $what
	 * @param  null|string $group_by
	 * @return string
	 */
	public function avg($what, $group_by=null) {
		return $this->_function('avg', $what, $group_by);
	}
	
	/**
	 * Return the sum.
	 * @param  string $what
	 * @param  null|string $group_by
	 * @return string
	 */
	public function sum($what, $group_by=null) {
		return $this->_function('sum', $what, $group_by);
	}
}
