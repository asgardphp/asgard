<?php
namespace Asgard\Db;

/**
 * Database Abstraction Layer.
 * @author Michel Hognerud <michel@hognerud.com>
 * @api
 */
class DAL implements \Iterator {
	/**
	 * Database instance.
	 * @var DBInterface
	 */
	protected $db;
	/**
	 * Paginator factory.
	 * @var \Asgard\Common\PaginatorFactoryInterface
	 */
	protected $paginatorFactory;
	/**
	 * Tables to access.
	 * @var array
	 */
	protected $tables  = [];
	/**
	 * Columns to access.
	 * @var array
	 */
	protected $columns = [];
	/**
	 * Where conditions.
	 * @var array
	 */
	protected $where   = [];
	/**
	 * Having conditions.
	 * @var array
	 */
	protected $having   = [];
	/**
	 * Jointures.
	 * @var array
	 */
	protected $joins   = [];
	/**
	 * Parameters.
	 * @var array
	 */
	protected $params  = [];
	/**
	 * Offset.
	 * @var integer
	 */
	protected $offset;
	/**
	 * Limit.
	 * @var integer
	 */
	protected $limit;
	/**
	 * Order by.
	 * @var array
	 */
	protected $orderBy;
	/**
	 * Group by.
	 * @var array
	 */
	protected $groupBy;
	/**
	 * Into table.
	 * @var string
	 */
	protected $into;
	/**
	 * Page number.
	 * @var integer
	 */
	protected $page;
	/**
	 * Number of rows per page.
	 * @var integer
	 */
	protected $per_page;
	/**
	 * Query instance.
	 * @var Query
	 */
	protected $query;
	/**
	 * Current row.
	 * @var array
	 */
	protected $current;
	/**
	 * UNIONs.
	 * @var array
	 */
	protected $unions = [];
	/**
	 * Insert ignore flag.
	 * @var boolean
	 */
	protected $ignore = false;

	/**
	 * Constructor.
	 * @param DBInterface  $db
	 * @param string  $tables
	 * @api
	 */
	public function __construct(DBInterface $db, $tables=null) {
		$this->db = $db;
		$this->addFrom($tables);
	}

	/**
	 * Reverse the query order.
	 * @return static
	 */
	public function reverse() {
		if(!$this->orderBy)
			throw new \Exception('Cannot reverse a query without order by.');

		preg_match_all('/([^,])*([(].*?[)])([^,])*|([^,])+/', $this->orderBy, $e);
		$e = $e[0];
		foreach($e as $k=>$v) {
			$v = preg_replace_callback('/(DESC|ASC)[\s]*/', function($r) {
				$r = $r[0];
				if(strpos($r, 'DESC') !== false)
					return str_replace('DESC', 'ASC', $r);
				else
					return str_replace('ASC', 'DESC', $r);
			}, $v, -1, $c);
			if($c === 0)
				$v = $v.' DESC';
			$e[$k] = $v;
		}
		$orderBy = implode(', ', $e);

		$this->orderBy($orderBy);
		return $this;
	}

	/**
	 * Return the last row.
	 * @return array
	 */
	public function last() {
		$c = clone $this;
		return $c->reverse()->first();
	}

	/**
	 * Return a raw SQL object.
	 * @param  string $sql
	 * @return Raw
	 * @api
	 */
	public static function raw($sql) {
		return new Raw($sql);
	}

	/**
	 * Return the DAL parameters.
	 * @return array
	 * @api
	 */
	public function getParameters() {
		return $this->params;
	}

	/**
	 * Set FROM tables.
	 * @param  string|array|dal $tables  Separated by ,
	 * @param string            $alias
	 * @return DAL              $this
	 * @api
	 */
	public function from($tables, $alias=null) {
		$this->tables = [];
		if(is_string($tables))
			return $this->addFrom($tables);
		else
			return $this->addFrom([$alias => $tables]);
	}

	/**
	 * INTO table.
	 * @param  string $table
	 * @return DAL    $this
	 * @api
	 */
	public function into($table) {
		$this->into = $table;
		return $this;
	}

	/**
	 * Add FROM tables.
	 * @param string|array|dal $tables Separated by ,
	 * @return DAL             $this
	 * @api
	 */
	public function addFrom($tables) {
		if(!$tables)
			return $this;

		if(is_string($tables))
			$tables = explode(',', $tables);
		elseif(!is_array($tables))
			$tables = [$tables];
		foreach($tables as $k=>$tablestr) {
			if($tablestr instanceof static) {
				$table = $tablestr;
				$alias = $k;
			}
			elseif($tablestr instanceof Raw) {
				$table = $tablestr;
				$alias = $k;
			}
			else {
				$tablestr = trim($tablestr);

				preg_match('/(.*?) ([a-z_][a-zA-Z0-9_]*)?$/i', $tablestr, $matches);
				if(isset($matches[2])) {
					$alias = $matches[2];
					$table = $matches[1];
				}
				else
					$alias = $table = $tablestr;

				if(isset($this->tables[$alias]))
					throw new \Exception('Table alias '.$alias.' is already used.');
			}
			
			$this->tables[$alias] = $table;
		}

		return $this;
	}

	/**
	 * Remove a FROM table.
	 * @param  string $what
	 * @return DAL   $this
	 * @api
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
	 * @param  string       $type
	 * @param  string|array $table
	 * @param  string|array $conditions
	 * @param  boolean      $recursive  can $table contain multiple jointures
	 * @return DAL          $this
	 */
	public function join($type, $table, $conditions=null, $recursive=true) {
		if($recursive && is_array($table)) {
			foreach($table as $_table=>$_conditions) {
				if($_conditions instanceof static)
					$this->join($type, $table, $conditions, false);
				elseif($_conditions instanceof Raw)
					$this->join($type, $table, $conditions, false);
				else
					$this->join($type, $_table, $_conditions);
			}

			return $this;
		}

		#for tables that are static or Raw
		if(is_array($table)) {
			$alias = array_keys($table)[0];
			$table = array_values($table)[0];
		}
		else {
			$table_alias = explode(' ', $table);
			$table = $table_alias[0];
			if(isset($table_alias[1]))
				$alias = $table_alias[1];
			else
				$alias = $table;
		}

		$this->joins[$alias] = [$type, $table, $conditions];

		return $this;
	}

	/**
	 * Add a LEFT jointure.
	 * @param  string|array $table
	 * @param  string|array $conditions
	 * @return DAL    $this
	 * @api
	 */
	public function leftjoin($table, $conditions=null) {
		return $this->join('leftjoin', $table, $conditions);
	}

	/**
	 * Add a RIGHT jointure.
	 * @param  string|array $table
	 * @param  string|array $conditions
	 * @return DAL    $this
	 * @api
	 */
	public function rightjoin($table, $conditions=null) {
		return $this->join('rightjoin', $table, $conditions);
	}

	/**
	 * Add an INNER jointure.
	 * @param  string|array $table
	 * @param  string|array $conditions
	 * @return DAL    $this
	 * @api
	 */
	public function innerjoin($table, $conditions=null) {
		return $this->join('innerjoin', $table, $conditions);
	}

	/**
	 * Return the next row.
	 * @return array
	 * @api
	 */
	public function next() {
		if($this->query === null)
			$this->query();
		return $this->current = $this->query->next();
	}

	public function current() {
		if(!$this->current)
			$this->next();
		return $this->current;
	}

	public function rewind() {
		if($this->query)
			$this->query($this->query->getSQL(), $this->query->getParameters());
		else
			$this->query();
	}

	public function key() {
		return;
	}

	public function valid() {
		return is_array($this->current());
	}

	/**
	 * Reset all parameters.
	 * @return DAL  $this
	 * @api
	 */
	public function reset() {
		$this->tables  = [];
		$this->columns = [];
		$this->where   = [];
		$this->offset  = null;
		$this->limit   = null;
		$this->orderBy = null;
		$this->groupBy = null;
		$this->joins   = [];
		$this->params  = [];

		return $this;
	}

	/**
	 * Execute a query.
	 * @param  string $sql
	 * @param  array  $params
	 * @return Query
	 * @api
	 */
	public function query($sql=null, array $params=[]) {
		if($sql === null) {
			$sql = $this->buildSQL();
			$params = $this->getParameters();
			return $this->query($sql, $params);
		}

		return $this->query = $this->db->query($sql, $params);
	}

	/**
	 * Return the first row only.
	 * @return array
	 * @api
	 */
	public function first() {
		if(!$this->query)
			$this->query();
		return $this->query->first();
	}

	/**
	 * Return selected rows.
	 * @return array
	 * @api
	 */
	public function get() {
		if(!$this->query)
			$this->query();
		return $this->query->all();
	}

	/**
	 * Paginate the results.
	 * @param  integer  $page
	 * @param  integer  $per_page
	 * @return DAL    $this
	 * @api
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
	 * @param \Asgard\Common\PaginatorFactoryInterface $paginatorFactory
	 * @return DAL                                     $this
	 * @api
	 */
	public function setPaginatorFactory(\Asgard\Common\PaginatorFactoryInterface $paginatorFactory) {
		$this->paginatorFactory = $paginatorFactory;
		return $this;
	}

	/**
	 * Get a paginator instance.
	 * @return \Asgard\Common\PaginatorInterface
	 * @api
	 */
	public function getPaginator() {
		if($this->page === null || $this->per_page === null)
			return;
		if(!$this->paginatorFactory)
			return new \Asgard\Common\Paginator($this->count(), $this->page, $this->per_page);
		return $this->paginatorFactory->create($this->count(), $this->page, $this->per_page);
	}

	/**
	 * Set SELECT columns.
	 * @param  string|array $columns
	 * @return DAL    $this
	 * @api
	 */
	public function select($columns) {
		$this->columns = [];
		return $this->addSelect($columns);
	}

	/**
	 * Add SELECT columns.
	 * @param string|array $columns
	 * @return DAL    $this
	 * @api
	 */
	public function addSelect($columns) {
		if(is_array($columns))
			return $this->_addSelect($columns);

		$columns = explode(',', $columns);
		foreach($columns as $columnstr) {
			$columnstr = trim($columnstr);

			preg_match('/(.*?)\s+as\s+([a-z_][a-zA-Z0-9_]*)?$/i', $columnstr, $matches);
			if(isset($matches[2])) {
				$alias = $matches[2];
				$column = $matches[1];
			}
			else
				$alias = $column = $columnstr;

			if(isset($this->columns[$alias]))
				throw new \Exception('Column alias '.$alias.' is already used.');
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
	 * @api
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
	 * @api
	 */
	public function offset($offset) {
		$this->offset = $offset;
		return $this;
	}

	/**
	 * Set limit.
	 * @param  integer $limit
	 * @return DAL    $this
	 * @api
	 */
	public function limit($limit) {
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Set order by.
	 * @param  string $orderBy
	 * @param  array  $parameters
	 * @return DAL    $this
	 * @api
	 */
	public function orderBy($orderBy, $parameters=[]) {
		$this->orderBy = [$orderBy, $parameters];
		return $this;
	}

	/**
	 * Set group by.
	 * @param  string $groupBy
	 * @param  array  $parameters
	 * @return DAL    $this
	 * @api
	 */
	public function groupBy($groupBy, $parameters=[]) {
		$this->groupBy = [$groupBy, $parameters];
		return $this;
	}

	/**
	 * Add WHERE conditions.
	 * @param  array|string $conditions
	 * @param  mixed $values
	 * @return DAL    $this
	 * @api
	 */
	public function where($conditions, $values=null) {
		if(!$conditions)
			return $this;
		if($values !== null)
			$this->where[] = [$conditions => $values];
		else
			$this->where[] = $conditions;

		return $this;
	}

	public function having($conditions, $values=null) {
		if(!$conditions)
			return $this;
		if($values !== null)
			$this->having[] = [$conditions => $values];
		else
			$this->having[] = $conditions;

		return $this;
	}

	/**
	 * Format the conditions.
	 * @param  array   $params
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
			#multiple conditions
			if(is_array($value) && (is_int($key) || $key === 'and' || $key === 'or' || $key === 'xor')) {
				if(is_int($key))
					$key = 'and';
				$r = $this->processConditions($value, $key, $brackets || count($params) > 1, $table);
				$string_conditions[] = $r[0];
				if(is_array($r[1]))
					$pdoparams = array_merge($pdoparams, $r[1]);
				else
					$pdoparams[] = $r[1];
			}
			else {
				if(is_int($key)) {
					if(static::isIdentifier($value))
						$string_conditions[] = $this->replace($value).' IS NULL';
					else
						$string_conditions[] = $this->replace($value);
				}
				elseif($key === 'not') {
					$r = $this->processConditions($value, 'and', true, $table);
					$string_conditions[] = 'NOT '.$r[0];
					if(is_array($r[1]))
						$pdoparams = array_merge($pdoparams, $r[1]);
					else
						$pdoparams[] = $r[1];
				}
				else {
					$res = $this->replace($key);
					if(is_array($value))
						$pdoparams = array_merge($pdoparams, $value);
					else {
						if(strpos($key, '?') === false) {
							#if we are checking that the identifier is equal to null
							if($value === null)
								$res = $key.' IS NULL';
							else {
								$res .= '=?';
								$pdoparams[] = $value;
							}
						}
						else
							$pdoparams[] = $value;
					}
					$string_conditions[] = $res;
				}
			}
		}

		$result = implode(' '.strtoupper($condition).' ', $string_conditions);

		if($brackets && count($params) > 1)
			$result = '('.$result.')';

		#prepare mulltiple parameters in arrays
		$result = preg_replace_callback('/\?/', function() use($pdoparams) {
			static $i=0;
			if(is_array($pdoparams[$i])) {
				if(count($pdoparams[$i]) === 0)
					return 'null';
				else
					return implode(',', array_fill(0, count($pdoparams[$i]), '?'));
				$i++;
			}
			else {
				$i++;
				return '?';
			}
		}, $result);

		$pdoparams = \Asgard\Common\ArrayUtils::flatten($pdoparams);

		return [$result, $pdoparams];
	}

	/**
	 * Remove a jointure.
	 * @param  string $alias
	 * @return DAL    $this
	 * @api
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
	protected function replace($condition, $setTable=true) {
		$condition = preg_replace_callback('/(?<![\.a-zA-Z0-9_`\(\)])[a-z_][a-zA-Z0-9._]*(?![^\(]*\))/', function($matches) use($setTable) {
			if($setTable && strpos($matches[0], '.')===false && count($this->joins) > 0 && count($this->tables)===1)
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
	public static function isIdentifier($str) {
		return (bool)preg_match('/^[a-z_][a-zA-Z0-9._]*$/', $str);
	}

	/**
	 * Quote idenfitiers.
	 * @param  string $str
	 * @return string
	 */
	protected function identifierQuotes($str) {
		#for every word
		return preg_replace_callback('/[a-z_][a-zA-Z0-9._]*/', function($matches) {
			$res = [];
			foreach(explode('.', $matches[0]) as $substr) {
				if(preg_match('/^[a-z_][a-zA-Z0-9_]*$/', $substr))
					$res[] = '`'.$substr.'`';
				else
					$res[] = $substr;
			}
			return implode('.', $res);
		}, $str);
	}

	/**
	 * Build the list of columns.
	 * @return string
	 */
	protected function buildColumns() {
		$select = [];
		$params = [];
		if(!$this->columns)
			return ['*', []];
		else {
			foreach($this->columns as $alias=>$column) {
				if($column instanceof static) {
					$sql = $column->buildSQL();
					$params = array_merge($params, $column->getParameters());
					$select[] = '('.$sql.') AS '.$this->identifierQuotes($alias);
				}
				elseif($column instanceof Raw)
					$select[] = $v.' AS '.$this->identifierQuotes($alias);
				elseif((string)$alias !== (string)$column)
					$select[] = $this->identifierQuotes($column).' AS '.$this->identifierQuotes($alias);
				else
					$select[] = $this->identifierQuotes($column);
			}
		}
		return [implode(', ', $select), $params];
	}

	/**
	 * Return the default table.
	 * @return string
	 */
	protected function getDefaultTable($real=false) {
		if(count($this->tables) === 1) {
			if($real)
				return array_values($this->tables)[0];
			else
				return array_keys($this->tables)[0];
		}
		else
			return null;
	}

	/**
	 * Build the WHERE conditions.
	 * @param  string $default
	 * @return array  1st element is the SQL, 2nd is the parameters.
	 */
	protected function buildWhere($default=null) {
		$r = $this->processConditions($this->where, 'and', false, $default!==null ? $default:$this->getDefaultTable());
		if($r[0])
			return ["\n".'WHERE '.$r[0], $r[1]];
		else
			return ['', []];
	}

	protected function buildHaving($default=null) {
		$r = $this->processConditions($this->having, 'and', false, $default!==null ? $default:$this->getDefaultTable());
		if($r[0])
			return ["\n".'HAVING '.$r[0], $r[1]];
		else
			return ['', []];
	}

	/**
	 * Build GROUP BY.
	 * @return array
	 */
	protected function buildGroupBy() {
		if(!$this->groupBy || !$this->groupBy[0])
			return ['', []];

		$groupBy = $this->groupBy;
		$groupBySql = $groupBy[0];
		$groupByParameters = $groupBy[1];

		$res = [];

		foreach(explode(',', $groupBySql) as $column) {
			if($this->isIdentifier(trim($column)))
				$res[] = $this->replace(trim($column));
			else
				$res[] = trim($column);
		}

		$sql = "\n".'GROUP BY '.implode(', ', $res);

		return [$sql, $groupByParameters];
	}

	/**
	 * Build ORDER By.
	 * @return array
	 */
	protected function buildOrderby() {
		if(!$this->orderBy || !$this->orderBy[0])
			return ['', []];

		$res = [];

		$orderBy = $this->orderBy;
		$orderBySql = $orderBy[0];
		$orderByParameters = $orderBy[1];

		foreach(explode(',', $orderBySql) as $orderbystr) {
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

		$sql = "\n".'ORDER BY '.implode(', ', $res);

		return [$sql, $orderByParameters];
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
				$jointure = "\n".'LEFT JOIN ';
				break;
			case 'rightjoin':
				$jointure = "\n".'RIGHT JOIN ';
				break;
			case 'innerjoin':
				$jointure = "\n".'INNER JOIN ';
				break;
		}

		if($table instanceof static) {
			$_table = '('.$table->buildSQL().') `'.$alias.'`';
			$params = array_merge($params, $table->getParameters());
			$table = $_table;
		}
		elseif($table instanceof Raw)
			$table = '('.$table.') `'.$alias.'`';
		else {
			if($alias !== null)
				$table = $table.' '.$alias;
			$table = preg_replace_callback('/(^[a-z_][a-zA-Z0-9._]*)| ([a-z_][a-zA-Z0-9._]*$)/', function($matches) {
					return $this->identifierQuotes($matches[0]);
			}, $table);
		}

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

		$limit = "\n".'LIMIT ';
		if($this->offset) {
			$limit .= $this->offset;
			if($this->limit)
				$limit .= ', '.$this->limit;
			else
				$limit .= ', '.PHP_INT_MAX;
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
	protected function buildTables($with_alias=true) {
		$tables = [];
		$params = [];
		if(!$this->tables)
			throw new \Exception('Must set tables with method from($tables) before running the query.');
		foreach($this->tables as $alias=>$table) {
			if($table instanceof static) {
				$tables[] = '('.$table->buildSQL().') `'.$alias.'`';
				$params = array_merge($params, $table->getParameters());
			}
			elseif($table instanceof Raw)
				$tables[] = '('.$table.') `'.$alias.'`';
			elseif($alias !== $table && $with_alias)
				$tables[] = '`'.$table.'` `'.$alias.'`';
			else
				$tables[] = '`'.$table.'`';
		}
		$sql = implode(', ', $tables);
		return [$sql, $params];
	}

	/**
	 * Replace ? with raw queries.
	 * @param  string &$sql
	 * @param  array  &$params
	 */
	protected function replaceRaws(&$sql, &$params) {
		$i = 0;
		$sql = preg_replace_callback('/\?/', function() use(&$i, &$params) {
			if($params[$i] instanceof static) {
				$r = $params[$i];
				$sql = $r->buildSQL();
				$params = array_merge(array_slice($params, 0, $i), $r->getParameters(), array_slice($params, $i+1));
				return '('.$sql.')';
			}
			if($params[$i] instanceof Raw) {
				$r = $params[$i];
				unset($params[$i]);
				return (string)$r;
			}
			else
				return '?';
		}, $sql);
	}

	/**
	 * Build a SELECT SQL query.
	 * @return string
	 * @api
	 */
	public function buildSQL($union=false) {
		$params = [];

		list($tables, $tableparams) = $this->buildTables();
		$params = array_merge($params, $tableparams);

		list($columns, $columnsparams) = $this->buildColumns();
		$params = array_merge($params, $columnsparams);

		list($jointures, $joinparams) = $this->buildJointures();
		$params = array_merge($params, $joinparams);

		list($where, $whereparams) = $this->buildWhere();
		$params = array_merge($params, $whereparams);

		list($groupBy, $groupByParams) = $this->buildGroupBy();
		$params = array_merge($params, $groupByParams);

		list($having, $havingparams) = $this->buildHaving();
		$params = array_merge($params, $havingparams);

		if(!$union) {
			$limit = $this->buildLimit();

			list($orderBy, $orderByParams) = $this->buildOrderBy();
			$params = array_merge($params, $orderByParams);
		}
		else
			$orderBy = $limit = '';

		$unions = '';
		foreach($this->unions as $union) {
			$unions .= "\n".'UNION ('.$union->buildSQL(true).')';
			$params = array_merge($params, $union->getParameters());
		}

		$sql = 'SELECT '.$columns.' FROM '.$tables.$jointures.$where.$groupBy.$having.$unions.$orderBy.$limit;

		$this->replaceRaws($sql, $params);

		$this->params = $params;
		return $sql;
	}

	/**
	 * Build an UPDATE SQL query.
	 * @param  array  $values
	 * @return string
	 * @api
	 */
	public function buildUpdateSQL(array $values) {
		if(count($values) == 0)
			throw new \Exception('Update values should not be empty.');
		$params = [];

		if($this->db->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'mysql') {
			list($jointures, $joinparams) = $this->buildJointures();
			$params = $joinparams;

			$set = [];
			foreach($values as $k=>$v) {
				if($v instanceof static) {
					$sql = $v->buildSQL();
					$params = array_merge($params, $v->getParameters());
					$set[] = $this->replace($k).'=('.$sql.')';
				}
				elseif($v instanceof Raw)
					$set[] = $this->replace($k).'='.$v;
				else {
					$params[] = $v;
					$set[] = $this->replace($k).'=?';
				}
			}
			$str = ' SET '.implode(', ', $set);

			list($tables, $tableparams) = $this->buildTables(true);
			$params = array_merge($params, $tableparams);

			#can't mix order by and joins in update
			if($this->joins)
				$orderBy = '';
			else {
				list($orderBy, $orderByParams) = $this->buildOrderBy();
				$params = array_merge($params, $orderByParams);
			}
			$limit = $this->buildLimit();

			list($where, $whereparams) = $this->buildWhere();
			$params = array_merge($params, $whereparams);
			$sql = 'UPDATE '.$tables.$jointures.$str.$where.$orderBy.$limit;

			$this->replaceRaws($sql, $params);
		}
		elseif($this->joins || $this->limit || $this->offset) {
			$set = [];
			foreach($values as $k=>$v) {
				if($v instanceof static) {
					$sql = $v->buildSQL();
					$params[] = array_merge($params, $v->getParameters());
					$set[] = $this->replace($k, false).'=('.$sql.')';
				}
				elseif($v instanceof Raw)
					$set[] = $this->replace($k, false).'='.$v;
				else {
					$params[] = $v;
					$set[] = $this->replace($k, false).'=?';
				}
			}
			$str = ' SET '.implode(', ', $set);

			$selectDal = clone $this;
			$selectDal->select('1')->from($this->getDefaultTable(true).' thisIsAUniqueAlias');

			$selectDal->replaceTable($this->getDefaultTable(), 'thisIsAUniqueAlias');

			foreach($this->db->getSchema()->table($this->getDefaultTable(true))->getColumns() as $colName=>$col)
				$selectDal->where('(thisIsAUniqueAlias.'.$colName.' IS NULL AND '.$this->getDefaultTable(true).'.'.$colName.' IS NULL OR thisIsAUniqueAlias.'.$colName.' = '.$this->getDefaultTable(true).'.'.$colName.')');

			$selectSql = $selectDal->buildSQL();

			$params = array_merge($params, $selectDal->getParameters());

			list($tables, $tableparams) = $this->buildTables(false);
			$params = array_merge($params, $tableparams);

			$sql = 'UPDATE '.$tables.$str."\n".'WHERE EXISTS ('.$selectSql.')';
		}
		else {
			$set = [];
			foreach($values as $k=>$v) {
				if($v instanceof static) {
					$sql = $v->buildSQL();
					$params[] = array_merge($params, $v->getParameters());
					$set[] = $this->replace($k, false).'=('.$sql.')';
				}
				elseif($v instanceof Raw)
					$set[] = $this->replace($k, false).'='.$v;
				else {
					$params[] = $v;
					$set[] = $this->replace($k, false).'=?';
				}
			}
			$str = ' SET '.implode(', ', $set);

			list($tables, $tableparams) = $this->buildTables(false);
			$params = array_merge($params, $tableparams);

			list($where, $whereparams) = $this->buildWhere();
			$params = array_merge($params, $whereparams);

			$sql = 'UPDATE '.$tables.$str.$where;
		}

		$this->params = $params;

		return $sql;
	}

	/**
	 * Build a DELETE SQL query.
	 * @param  string[] $del_tables
	 * @return string
	 * @api
	 */
	public function buildDeleteSQL(array $del_tables=[]) {
		$params = [];

		if($this->db->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'mysql') {
			list($tables, $tableparams) = $this->buildTables(count($del_tables) > 0);
			$params = array_merge($params, $tableparams);

			list($jointures, $joinparams) = $this->buildJointures();
			$params = array_merge($params, $joinparams);

			list($where, $whereparams) = $this->buildWhere();
			$params = array_merge($params, $whereparams);

			#if multiple tables to delete from
			if($del_tables) {
				foreach($del_tables as $k=>$v)
					$del_tables[$k] = '`'.$v.'`';
				$del_tables = implode(', ', $del_tables);
				$sql = 'DELETE '.$del_tables.' FROM '.$tables.$jointures.$where;
			}
			#jointures and given table to delete from
			#jointures require to say from which table to delete. if no table given, use the default table.
			elseif($this->joins && !$del_tables) {
				$del_table = '`'.$this->getDefaultTable().'`';
				$sql = 'DELETE '.$del_table.' FROM '.$tables.$jointures.$where;
			}
			else
				$sql = 'DELETE FROM '.$tables.$jointures.$where;

			#can use orderBy and limit only if there is no jointure
			if(!$jointures) {
				list($orderBy, $orderByParams) = $this->buildOrderBy();
				$params = array_merge($params, $orderByParams);

				$limit = $this->buildLimit();

				$sql .= $orderBy.$limit;
			}

			$this->replaceRaws($sql, $params);
		}
		elseif($this->joins || $this->limit || $this->offset) {
			$selectDal = clone $this;
			$selectDal->select('1')->from($this->getDefaultTable(true).' thisIsAUniqueAlias');

			$selectDal->replaceTable($this->getDefaultTable(), 'thisIsAUniqueAlias');

			foreach($this->db->getSchema()->table($this->getDefaultTable(true))->getColumns() as $colName=>$col)
				$selectDal->where('(thisIsAUniqueAlias.'.$colName.' IS NULL AND '.$this->getDefaultTable(true).'.'.$colName.' IS NULL OR thisIsAUniqueAlias.'.$colName.' = '.$this->getDefaultTable(true).'.'.$colName.')');
			$selectSql = $selectDal->buildSQL();

			$params = array_merge($params, $selectDal->getParameters());

			list($tables, $tableparams) = $this->buildTables(false);
			$params = array_merge($params, $tableparams);

			$sql = 'DELETE FROM '.$tables."\n".'WHERE EXISTS ('.$selectSql.')';
		}
		else {
			list($tables, $tableparams) = $this->buildTables(false);
			$params = array_merge($params, $tableparams);

			list($where, $whereparams) = $this->buildWhere();
			$params = array_merge($params, $whereparams);

			$sql = 'DELETE FROM '.$tables.$where;
		}

		$this->params = $params;

		return $sql;
	}

	/**
	 * Replace an alias in conditions.
	 * @param  array  $conditions
	 * @param  string $oldTable
	 * @param  string $newTable
	 * @return array  new conditions
	 * @api
	 */
	public function replaceTableInConditions($conditions, $oldTable, $newTable) {
		foreach($conditions as $k=>$v) {
			if(is_array($v))
				$v = $this->replaceTableInConditions($v, $oldTable, $newTable);
			else
				$v = preg_replace('/(?<![a-zA-Z0-9_])'.$oldTable.'\./', $newTable.'.', $v);

			if(is_string($k)) {
				$newK = preg_replace('/(?<![a-zA-Z0-9_])'.$oldTable.'\./', $newTable.'.', $k);
				unset($conditions[$k]);
				$conditions[$newK] = $v;
			}
			else
				$conditions[$k] = $v;
		}
		return $conditions;
	}

	/**
	 * Replace an alias with a new one in the conditions, jointures and group by.
	 * @param string $oldTable
	 * @param string $newTable
	 * @api
	 */
	public function replaceTable($oldTable, $newTable) {
		$this->where = $this->replaceTableInConditions($this->where, $oldTable, $newTable);
		$this->joins = $this->replaceTableInConditions($this->joins, $oldTable, $newTable);
		#using regex to replace table names not preceded by one of the following character
		if($this->groupBy !== null)
			$this->groupBy[0] = preg_replace('/(?<![a-zA-Z0-9_])'.$oldTable.'\./', $newTable.'.', $this->groupBy[0]);
	}

	/**
	 * Build an INSERT SQL query.
	 * @param  array  $values
	 * @param  array  $update
	 * @return string
	 * @api
	 */
	public function buildInsertSQL(array $rows, array $update=[]) {
		if($this->into === null && count($this->tables) !== 1)
			throw new \Exception('The into table is not defined.');
		if($this->into !== null)
			$into = $this->into;
		else
			$into = array_keys($this->tables)[0];

		$params = [];
		$into = $this->identifierQuotes($into);

		$cols = [];
		foreach($rows[0] as $k=>$v)
			$cols[] = $this->replace($k, false);

		$strs = [];
		foreach($rows as $values) {
			if(count($values) == 0)
				throw new \Exception('Insert values should not be empty.');

			foreach($values as $k=>&$v) {
				if($v instanceof static) {
					$sql = $v->buildSQL();
					$params = array_merge($params, $v->getParameters());
					$v = '('.$sql.')';
				}
				elseif($v instanceof Raw)
					$v = (string)$v;
				else {
					$params[] = $v;
					$v = '?';
				}
			}

			$colsstr = ' ('.implode(', ', $cols).') VALUES ';
			$strs[] = '('.implode(', ', $values).')';
		}
		$str = implode(', ', $strs);

		$sql = 'INSERT'.($this->ignore ? ' IGNORE':'').' INTO '.$into.$colsstr.$str;
		if(count($update) > 0) {
			$set = [];
			foreach($update as $k=>$v) {
				if($v instanceof static) {
					$sql = $v->buildSQL();
					$params = array_merge($params, $v->getParameters());
					$set[] = $this->replace($k).'=('.$sql.')';
				}
				elseif($v instanceof Raw)
					$set[] = $this->replace($k).'='.$v;
				else {
					$params[] = $v;
					$set[] = $this->replace($k).'=?';
				}
			}
			$str = implode(', ', $set);

			$sql .= ' ON DUPLICATE KEY UPDATE '.$str;
		}

		$this->replaceRaws($sql, $params);
		$this->params = $params;

		return $sql;
	}

	/**
	 * Return all values of a column.
	 * @param  string $column
	 * @return array
	*/
	public function values($column) {
		$res = [];
		while($row = $this->next())
			$res[] = $row[$column];
		return $res;
	}

	/**
	 * Update rows.
	 * @param  array  $values
	 * @return integer
	 * @api
	 */
	public function update(array $values) {
		$sql = $this->buildUpdateSQL($values);
		$params = $this->getParameters();
		return $this->db->query($sql, $params)->affected();
	}

	/**
	 * Insert rows.
	 * @param  array  $values
	 * @param  array  $update
	 * @return integer
	 * @api
	 */
	public function insert(array $values, array $update=[]) {
		$sql = $this->buildInsertSQL([$values], $update);
		$params = $this->getParameters();
		$this->db->query($sql, $params);
		return $this->db->id();
	}

	/**
	 * Delete rows.
	 * @param  string[] $tables
	 * @return integer
	 * @api
	 */
	public function delete(array $tables=[]) {
		$sql = $this->buildDeleteSQL($tables);
		$params = $this->getParameters();
		return $this->db->query($sql, $params)->affected();
	}

	/**
	 * Execute a math function.
	 * @param  string       $fct
	 * @param  string       $what
	 * @param  string       $group_by
	 * @return array|string
	 */
	protected function _function($fct, $what=null, $group_by=null) {
		if($what === null)
			$what = '*';
		$fct = strtoupper($fct);

		$clone = clone $this;
		if($fct === 'COUNT') {
			$clone->offset(null);
			$clone->limit(null);
		}
		if($group_by) {
			$dal = new static($this->db);
			$dal->select($group_by.' groupby, '.$fct.'('.$what.') '.$fct)
			    ->groupBy($group_by)
			    ->from($clone, 's');
			$res = [];
			foreach($dal->get() as $v)
				$res[$v['groupby']] = $v[$fct];
			return $res;
		}
		else {
			$dal = new static($this->db);
			$dal->select($fct.'('.$what.') '.$fct)
			    ->from($clone, 's');
			return \Asgard\Common\ArrayUtils::array_get($dal->first(), $fct);
		}
	}

	/**
	 * Count number of rows.
	 * @param  string $what
	 * @param  string $group_by
	 * @return string
	 * @api
	 */
	public function count($what=null, $group_by=null) {
		$r = $this->_function('COUNT', $what, $group_by);
		if(!is_array($r))
			$r = (int)$r;
		return $r;
	}

	/**
	 * Return the minimum value.
	 * @param  string $what
	 * @param  string $group_by
	 * @return string
	 * @api
	 */
	public function min($what, $group_by=null) {
		return $this->_function('MIN', $what, $group_by);
	}

	/**
	 * Return the maximum value.
	 * @param  string $what
	 * @param  string $group_by
	 * @return string
	 * @api
	 */
	public function max($what, $group_by=null) {
		return $this->_function('MAX', $what, $group_by);
	}

	/**
	 * Return the average value.
	 * @param  string $what
	 * @param  string $group_by
	 * @return string
	 * @api
	 */
	public function avg($what, $group_by=null) {
		return $this->_function('AVG', $what, $group_by);
	}

	/**
	 * Return the sum.
	 * @param  string $what
	 * @param  string $group_by
	 * @return string
	 * @api
	 */
	public function sum($what, $group_by=null) {
		return $this->_function('SUM', $what, $group_by);
	}

	/**
	 * Replace parameters in a SQL query.
	 * @param  string $sql
	 * @param  array  $params
	 * @return string
	 */
	protected function replaceParams($sql, array $params) {
		$i=0;
		return preg_replace_callback('/\?/', function() use(&$i, $params, $sql) {
			$rep = $params[$i++];
			if(!$rep instanceof Raw && !$rep instanceof static)
				return "'".addslashes($rep)."'";
			else
				return '?';
		}, $sql);
	}

	/**
	 * Compute the sql query for debugging.
	 * @return string
	 */
	public function dbgSelect() {
		$sql = $this->buildSQL();
		$params = $this->getParameters();

		return $this->replaceParams($sql, $params);
	}

	/**
	 * Compute the update sql query for debugging.
	 * @param  array  $values
	 * @return string
	 */
	public function dbgUpdate(array $values) {
		$sql = $this->buildUpdateSQL($values);
		$params = $this->getParameters();

		return $this->replaceParams($sql, $params);
	}

	/**
	 * Conpute the insert sql query for debugging.
	 * @param  array  $values
	 * @param  array  $update
	 * @return string
	 */
	public function dbgInsert(array $values, array $update=[]) {
		$sql = $this->buildInsertSQL([$values], $update);
		$params = $this->getParameters();

		return $this->replaceParams($sql, $params);
	}

	/**
	 * Conpute the insert many sql query for debugging.
	 * @param  array  $rows
	 * @param  array  $update
	 * @return string
	 */
	public function dbgInsertMany(array $rows, array $update=[]) {
		$sql = $this->buildInsertSQL($rows, $update);
		$params = $this->getParameters();

		return $this->replaceParams($sql, $params);
	}

	/**
	 * Compute the delete sql query for debugging.
	 * @return string
	 */
	public function dbgDelete(array $tables=[]) {
		$sql = $this->buildDeleteSQL($tables);
		$params = $this->getParameters();

		return $this->replaceParams($sql, $params);
	}

	/**
	 * Add UNIONs.
	 * @param  array|static $dals
	 * @return DAL
	 * @api
	 */
	public function union($dals) {
		if(!is_array($dals))
			$dals = [$dals];
		$this->unions = array_merge($this->unions, $dals);

		return $this;
	}

	/**
	 * Insert rows.
	 * @param  array  $rows
	 * @param  array  $update
	 * @return integer
	 * @api
	 */
	public function insertMany(array $rows, array $update=[]) {
		$sql = $this->buildInsertSQL($rows, $update);
		$params = $this->getParameters();
		$this->db->query($sql, $params);
		return $this->db->id();
	}

	/**
	 * Ignore duplicate errors on insertion.
	 * @param  boolean $ignore
	 * @return static
	 * @api
	 */
	public function setIgnore($ignore) {
		$this->ignore = $ignore;
		return $this;
	}
}
