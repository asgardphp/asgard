<?php
namespace Asgard\Entity\Property;

/**
 * Decimal Property.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class DecimalProperty extends \Asgard\Entity\Property {
	/**
	 * {@inheritDoc}
	 */
	public function doSet($val, \Asgard\Entity\Entity $entity, $name) {
		if($val === null || $val === false || $val === '')
			return null;
		return (double)$val;
	}

	/**
	 * Return parameters for ORM.
	 * @return array
	 */
	public function getORMParameters() {
		return [
			'type' => 'decimal',
			'precision' => $this->get('precision') ? $this->get('precision'):20,
			'scale' => $this->get('scale') ? $this->get('scale'):6,
		];
	}

	/**
	 * Return prepared input for SQL.
	 * @param  mixed $val
	 * @return double
	 */
	public function toSQL($val) {
		return (double)$val;
	}

	/**
	 * Transform SQL output.
	 * @param  mixed $val
	 * @return boolean
	 */
	public function fromSQL($val) {
		return (double)$val;
	}
}