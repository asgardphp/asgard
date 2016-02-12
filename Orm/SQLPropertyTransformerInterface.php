<?php
namespace Asgard\Orm;

interface SQLPropertyTransformerInterface {
	/**
	 * Convert to SQL input.
	 * @param  mixed $val
	 * @return mixed
	 */
	public function toSQL($val);
	/**
	 * Convert from SQL output.
	 * @param  mixed $val
	 * @return mixed
	 */
	public function fromSQL($val);
}