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
	public function getORMParameters() {
		return [
			'type' => 'integer',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function doSet($val, \Asgard\Entity\Entity $entity, $name) {
		if($val === null || $val === false || $val === '')
			return null;
		return (int)$val;
	}
}