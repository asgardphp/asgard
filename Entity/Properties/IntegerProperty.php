<?php
namespace Asgard\Entity\Properties;

/**
 * Integer Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class IntegerProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function doSet($val, \Asgard\Entity\Entity $entity, $name) {
		if($val === null || $val === false || $val === '')
			return null;
		return (int)$val;
	}

	/**
	 * Return parameters for ORM.
	 * @return array
	 */
	public function getORMParameters() {
		return [
			'type' => 'integer',
		];
	}

	/**
	 * Return prepared input for SQL.
	 * @param  mixed $val
	 * @return integer
	 */
	public function toSQL($val) {
		return (int)$val;
	}

	/**
	 * Transform SQL output.
	 * @param  mixed $val
	 * @return boolean
	 */
	public function fromSQL($val) {
		return (int)$val;
	}
}