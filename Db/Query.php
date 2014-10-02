<?php
namespace Asgard\Db;

/**
 * Query objects.
 */
class Query {
	/**
	 * Database instance.
	 * @var \PDO
	 */
	protected $db;
	/**
	 * Query's ressource.
	 * @var \PDOStatement
	 */
	protected $rsc;

	/**
	 * Constructor.
	 * @param \PDO    $db
	 * @param string  $sql
	 * @param array   $args
	 */
	public function __construct(\PDO $db, $sql, array $args=[]) {
		$this->db = $db;
		try {
			if($args) {
				$this->rsc = $db->prepare($sql);
				$this->rsc->execute($args);
			}
			else
				$this->rsc = $db->query($sql);
		} catch(\PDOException $e) {
			throw new DBException($e->getMessage().'<br/>'."\n".'SQL: '.$sql.' ('.implode(', ', $args).')'); #todo extend pdoexception?
		}
	}
	
	/**
	 * Return the next row.
	 * @return array
	 */
	public function next() {
		return $this->rsc->fetch(\PDO::FETCH_ASSOC);
	}

	/**
	 * Return the number of affected rows.
	 * @return integer
	 */
	public function affected() {
		return $this->rsc->rowCount();
	}

	/**
	 * Count the results.
	 * @return integer
	 */
	public function count() {
		return $this->rsc->rowCount();
	}

	/**
	 * Return the first row only.
	 * @return array
	 */
	public function first() {
		return $this->rsc->fetch(\PDO::FETCH_ASSOC);
	}

	/**
	 * Return all rows at once.
	 * @return array
	 */
	public function all() {
		return $this->rsc->fetchAll(\PDO::FETCH_ASSOC);
	}
}