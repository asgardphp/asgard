<?php
namespace Asgard\Db;

/**
 * Database Abstraction Layer.
 * @author Michel Hognerud <michel@hognerud.com>
 * @api
 */
class DAL {
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
	 * @var string
	 */
	protected $orderBy;
	/**
	 * Group by.
	 * @var string
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
	 * @param  string $tables  Separated by ,
	 * @return DAL    $this
	 * @api
	 */
	public function from($tables) {
		$this->tables = [];
		return $this->addFrom($tables);
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
	 * @param string $tables  Separated by ,
	 * @return DAL  $this
	 * @api
	 */
	public function addFrom($tables) {
		if(!$tables)
			return $this;

		$tables = explode(',', $tables);
		foreach($tables as $tablestr) {
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
			$this->tables[$alias] = $table;
		}

		return $this;
	}

	/**
	 * Remove a FROM table.
	 * @param  string $what
	 * @return DALm   $this
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
	 * @return DAL          $this
	 */
	protected function join($type, $table, $conditions=null) {
		if(is_array($table)) {
			foreach($table as $_table=>$_conditions)
				$this->join($type, $_table, $_conditions);
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
			$this->query = $this->query();
		return $this->query->next();
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

		return $this->db->query($sql, $params);
	}

	/**
	 * Return the first row only.
	 * @return array
	 * @api
	 */
	public function first() {
		return $this->query()->first();
	}

	/**
	 * Return selected rows.
	 * @return array
	 * @api
	 */
	public function get() {
		return $this->query()->all();
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
		return $this->paginatorFactory->create([$this->count(), $this->page, $this->per_page]);
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

			preg_match('/(.*?)\s*as\s*([a-z_][a-zA-Z0-9_]*)?$/i', $columnstr, $matches);
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
	 * @return DAL    $this
	 * @api
	 */
	public function orderBy($orderBy) {
		$this->orderBy = $orderBy;
		return $this;
	}

	/**
	 * Set group by.
	 * @param  string $groupBy
	 * @return DAL    $this
	 * @api
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
	 * @api
	 */
	public function where($conditions, $values=null) {
		if($values !== null)
			$this->where[] = [$conditions => $values];
		else
			$this->where[] = $conditions;

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
			if(is_array($value) && (is_int($key) || $key == 'and' || $key == 'or')) {
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
				else {
					$res = $this->replace($key);
					if(is_array($value))
						$pdoparams = array_merge($pdoparams, $value);
					else {
						if(strpos($key, '?') === false)
							$res .= '=?';
						$pdoparams[] = $value;
					}
					$string_conditions[] = $res;
				}
			}
		}

		$result = implode(' '.strtoupper($condition).' ', $string_conditions);

		if($brackets && count($params) > 1)
			$result = '('.$result.')';

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
		$condition = preg_replace_callback('/[a-z_][a-zA-Z0-9._]*(?![^\(]*\))/', function($matches) use($setTable) {
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
		return preg_match('/^[a-z_][a-zA-Z0-9._]*$/', $str);
	}

	/**
	 * Quote idenfitiers.
	 * @param  string $str
	 * @return string
	 */
	protected function identifierQuotes($str) {
		#for every word
		return preg_replace_callback('/(?<=\s|^)[a-zA-Z0-9._]*/', function($matches) {
			#if the word in not only uppercase letters (sql keyword)
			if(!preg_match('/[a-z0-9._]/', $matches[0]))
				return $matches[0];
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
		$table = preg_replace_callback('/(^[a-z_][a-zA-Z0-9._]*)| ([a-z_][a-zA-Z0-9._]*$)/', function($matches) {
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

	protected function replaceRaws(&$sql, &$params) {
		$i = 0;
		$sql = preg_replace_callback('/\?/', function($match) use(&$i, $params) {
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

		$sql = 'SELECT '.$columns.' FROM '.$tables.$jointures.$where.$groupby.$orderBy.$limit;

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

		if($this->db->getConn()->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MySqlPlatform) {
			$set = [];
			foreach($values as $k=>$v) {
				if($v instanceof Raw)
					$set[] = $this->replace($k).'='.$v;
				else {
					$params[] = $v;
					$set[] = $this->replace($k).'=?';
				}
			}
			$str = ' SET '.implode(', ', $set);

			$tables = $this->buildTables(true);
			$orderBy = $this->buildOrderBy();
			$limit = $this->buildLimit();

			list($jointures, $joinparams) = $this->buildJointures();
			$params = array_merge($params, $joinparams);

			list($where, $whereparams) = $this->buildWhere();
			$params = array_merge($params, $whereparams);
			$sql = 'UPDATE '.$tables.$jointures.$str.$where.$orderBy.$limit;

			$this->replaceRaws($sql, $params);
		}
		elseif($this->joins || $this->limit || $this->offset) {
			$set = [];
			foreach($values as $k=>$v) {
				if($v instanceof Raw)
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
				$selectDal->where('thisIsAUniqueAlias.'.$colName.' <> '.$this->getDefaultTable(true).'.'.$colName);

			$selectSql = $selectDal->buildSQL();

			$params = array_merge($params, $selectDal->getParameters());

			$tables = $this->buildTables(false);

			$sql = 'UPDATE '.$tables.$str.' WHERE EXISTS ('.$selectSql.')';
		}
		else {
			$set = [];
			foreach($values as $k=>$v) {
				if($v instanceof Raw)
					$set[] = $this->replace($k, false).'='.$v;
				else {
					$params[] = $v;
					$set[] = $this->replace($k, false).'=?';
				}
			}
			$str = ' SET '.implode(', ', $set);

			$tables = $this->buildTables(false);
			list($where, $whereparams) = $this->buildWhere();
			$params = array_merge($params, $whereparams);

			$sql = 'UPDATE '.$tables.$str.$where;
		}

		$this->params = $params;

		return $sql;
	}

	/**
	 * Build a DELETE SQL query.
	 * @param  array $del_tables
	 * @return string
	 * @api
	 */
	public function buildDeleteSQL(array $del_tables=[]) {
		$params = [];

		if($this->db->getConn()->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MySqlPlatform) {
			$tables = $this->buildTables(count($del_tables) > 0);
			$orderBy = $this->buildOrderBy();
			$limit = $this->buildLimit();

			list($jointures, $joinparams) = $this->buildJointures();
			$params = array_merge($params, $joinparams);

			list($where, $whereparams) = $this->buildWhere();
			$params = array_merge($params, $whereparams);

			if($this->db->getConn()->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MySqlPlatform && $del_tables) {
				foreach($del_tables as $k=>$v)
					$del_tables[$k] = '`'.$v.'`';
				$del_tables = implode(', ', $del_tables);
				$sql = 'DELETE '.$del_tables.' FROM '.$tables.$jointures.$where.$orderBy.$limit;
			}
			else
				$sql = 'DELETE FROM '.$tables.$jointures.$where.$orderBy.$limit;

			$this->replaceRaws($sql, $params);
		}
		elseif($this->joins || $this->limit || $this->offset) {
			$selectDal = clone $this;
			$selectDal->select('1')->from($this->getDefaultTable(true).' thisIsAUniqueAlias');

			$selectDal->replaceTable($this->getDefaultTable(), 'thisIsAUniqueAlias');

			foreach($this->db->getSchema()->table($this->getDefaultTable(true))->getColumns() as $colName=>$col)
				$selectDal->where('thisIsAUniqueAlias.'.$colName.' <> '.$this->getDefaultTable(true).'.'.$colName);
			$selectSql = $selectDal->buildSQL();

			$params = array_merge($params, $selectDal->getParameters());

			$tables = $this->buildTables(false);

			$sql = 'DELETE FROM '.$tables.' WHERE EXISTS ('.$selectSql.')';
		}
		else {
			$tables = $this->buildTables(false);
			list($where, $whereparams) = $this->buildWhere();
			$params = array_merge($params, $whereparams);

			$sql = 'DELETE FROM '.$tables.$where;
		}

		$this->params = $params;

		return $sql;
	}

	/**
	 * Replace an alias in conditions.
	 * @param  aray   $conditions
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
				$v = str_replace($oldTable.'.', $newTable.'.', $v);

			if(is_string($k)) {
				$newK = str_replace($oldTable.'.', $newTable.'.', $k);
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
		#todo careful that the name is not just the suffix of another one
		$this->where = $this->replaceTableInConditions($this->where, $oldTable, $newTable);
		$this->joins = $this->replaceTableInConditions($this->joins, $oldTable, $newTable);
		$this->groupBy = str_replace($oldTable.'.', $newTable.'.', $this->groupBy);
	}

	/**
	 * Build an INSERT SQL query.
	 * @param  array  $values
	 * @return string
	 * @api
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
		foreach($values as $k=>&$v) {
			$cols[] = $this->replace($k, false);
			if($v instanceof Raw)
				$v = (string)$v;
			else {
				$params[] = $v;
				$v = '?';
			}
		}
		$str = ' ('.implode(', ', $cols).') VALUES ('.implode(', ', $values).')';

		$sql = 'INSERT INTO '.$into.$str;

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
		$this->select($this->replace($column));
		while($row = $this->next())
			$res[] = $row[$column];
		return $res;
	}

	/**
	 * Update rows.
	 * @param  array  $values
	 * @return Query
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
	 * @return Query
	 * @api
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
	 * @api
	 */
	public function delete(array $tables=[]) {
		$sql = $this->buildDeleteSQL($tables);
		$params = $this->getParameters();
		return $this->db->query($sql, $params)->affected();
	}

	/**
	 * Execute a math function.
	 * @param  string      $fct
	 * @param  string $what
	 * @param  string $group_by
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
	 * @param  string $group_by
	 * @return string
	 * @api
	 */
	public function count($what=null, $group_by=null) {
		return $this->_function('count', $what, $group_by);
	}

	/**
	 * Return the minimum value.
	 * @param  string      $what
	 * @param  string $group_by
	 * @return string
	 * @api
	 */
	public function min($what, $group_by=null) {
		return $this->_function('min', $what, $group_by);
	}

	/**
	 * Return the maximum value.
	 * @param  string      $what
	 * @param  string $group_by
	 * @return string
	 * @api
	 */
	public function max($what, $group_by=null) {
		return $this->_function('max', $what, $group_by);
	}

	/**
	 * Return the average value.
	 * @param  string $what
	 * @param  string $group_by
	 * @return string
	 * @api
	 */
	public function avg($what, $group_by=null) {
		return $this->_function('avg', $what, $group_by);
	}

	/**
	 * Return the sum.
	 * @param  string $what
	 * @param  string $group_by
	 * @return string
	 * @api
	 */
	public function sum($what, $group_by=null) {
		return $this->_function('sum', $what, $group_by);
	}
}
