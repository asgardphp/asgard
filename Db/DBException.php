<?php
namespace Asgard\Db;

/**
 * Database Exception.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class DBException extends \PDOException {
	/**
	 * SQL.
	 * @var string
	 */
	protected $sql;
	/**
	 * SQL arguments.
	 * @var array
	 */
	protected $args;

	/**
	 * [feedPDOException description]
	 * @param  \PDOException $e
	 * @return static
	 */
	public function feedPDOException(\PDOException $e) {
		$this->errorInfo = $e->errorInfo;
		$this->message = $e->getMessage();
		$this->code = $e->getCode();
		$this->file = $e->getFile();
		$this->line = $e->getLine();
		return $this;
	}

	/**
	 * Set SQL.
	 * @param string $sql
	 * @param array $args
	 * @return static
	 */
	public function setSQL($sql, array $args=[]) {
		$this->sql = $sql;
		$this->args = $args;

		$this->message = $this->getMessage();

		if(strlen($sql) > 2048)
			return $this;
		$msg = '<br/>'."\n".'SQL: '.$sql;
		if(count($args) > 0) {
			$msg .= ' ('.implode(', ', $args).')';
			if(strlen($msg) > 2048)
				return $this;
		}
		$this->message .= $msg;

		return $this;
	}
}