<?php
namespace Asgard\Db;

class Raw {
	/**
	 * SQL.
	 * @var string
	 */
	protected $sql;

	/**
	 * Constructor.
	 * @param string $sql
	 */
	public function __construct($sql) {
		$this->sql = $sql;
	}

	/**
	 * __toString magic method.
	 * @return string
	 */
	public function __toString() {
		return $this->sql;
	}
}