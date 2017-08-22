<?php
namespace Asgard\Db;

class Raw {
	/**
	 * SQL.
	 * @var string
	 */
	protected $sql;
	/**
	 * Parameters
	 * @var array
	 */
	protected $parameters;

	/**
	 * Constructor.
	 * @param string $sql
	 */
	public function __construct($sql, array $parameters=[]) {
		$this->sql = $sql;
		$this->parameters = $parameters;
	}

	/**
	 * Get SQL.
	 * @return string
	 */
	public function getSQL() {
		return $this->sql;
	}

	/**
	 * Get parameters.
	 * @return array
	 */
	public function getParameters() {
		return $this->parameters;
	}

	/**
	 * __toString magic method.
	 * @return string
	 */
	public function __toString() {
		return $this->sql;
	}
}